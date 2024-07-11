<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 16/04/2019 à 15:56
 */

namespace Aropixel\PageBundle\EventListener;


use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\ReflectionService;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Webmozart\Assert\Assert;


class MappedSuperClassListener
{

    const TRANSLATABLE_ENTITIES = [
        'Aropixel\PageBundle\Entity\PageTranslatable',
        'Aropixel\PageBundle\Entity\PageTranslation',
        'Aropixel\PageBundle\Entity\FieldTranslatable',
        'Aropixel\PageBundle\Entity\FieldTranslation',
    ];

    private RuntimeReflectionService $reflectionService;
    private array $entitiesNames;


    public function __construct(
        $entities,
        protected readonly ParameterBagInterface $parameterBag
    ){
        $this->entitiesNames = $entities;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $metadata = $eventArgs->getClassMetadata();

        foreach ($this->entitiesNames as $interface => $model) {

            $isTranslatable = $this->parameterBag->has('translatable') && $this->parameterBag->get('translatable');

            if (!$isTranslatable && in_array($metadata->getName(), self::TRANSLATABLE_ENTITIES)) {

                $this->unsetAssociationMappings($metadata);

            } elseif ($metadata->getName() == $model) {

                if (!$metadata->isMappedSuperclass) {
                    $this->setAssociationMappings($metadata, $eventArgs->getEntityManager()->getConfiguration());
                }
                else {
                    $metadata->isMappedSuperclass = false;
                }

            } else {

                if (in_array($interface, class_implements($metadata->getName()))) {
                    $this->unsetAssociationMappings($metadata);
                }

            }

        }

    }

    private function setAssociationMappings(ClassMetadata $metadata, Configuration $configuration): void
    {
        $class = $metadata->getName();

        if (!class_exists($class)) {
            return;
        }

        $metadataDriver = $configuration->getMetadataDriverImpl();
        Assert::isInstanceOf($metadataDriver, MappingDriver::class);

        foreach (class_parents($class) as $parent) {
            if (false === in_array($parent, $metadataDriver->getAllClassNames(), true)) {
                continue;
            }

            $parentMetadata = new ClassMetadata(
                $parent,
                $configuration->getNamingStrategy()
            );

            // Wakeup Reflection
            $parentMetadata->wakeupReflection($this->getReflectionService());

            // Load Metadata
            $metadataDriver->loadMetadataForClass($parent, $parentMetadata);

            if ($parentMetadata->isMappedSuperclass) {
                foreach ($parentMetadata->getAssociationMappings() as $key => $value) {
                    if ($this->isRelation($value['type']) && !isset($metadata->associationMappings[$key])) {
                        $metadata->associationMappings[$key] = $value;
                    }
                }
            }
        }
    }

    private function unsetAssociationMappings(ClassMetadata $metadata): void
    {
        foreach ($metadata->getAssociationMappings() as $key => $value) {
            if ($this->isRelation($value['type'])) {
                unset($metadata->associationMappings[$key]);
            }
        }
    }

    private function isRelation(int $type): bool
    {
        return in_array(
            $type,
            [
                ClassMetadata::MANY_TO_MANY,
                ClassMetadata::ONE_TO_MANY,
                ClassMetadata::ONE_TO_ONE,
            ],
            true
        );
    }

    protected function getReflectionService(): ReflectionService
    {
        if ($this->reflectionService === null) {
            $this->reflectionService = new RuntimeReflectionService();
        }

        return $this->reflectionService;
    }
}
