<?php

namespace Aropixel\PageBundle\Form\Type;

use Aropixel\AdminBundle\Form\Type\TranslatableType;
use Aropixel\PageBundle\Entity\PageInterface;
use Aropixel\PageBundle\Entity\PageTranslation;
use Aropixel\PageBundle\Form\DataMapper\PageFieldDataMapper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


abstract class AbstractPageType extends AbstractType implements PageFormTypeInterface
{
    public const ALL_RIGHTS_MODE = 'ROLE_SUPER_ADMIN';
    protected string $pageClass;

    abstract public function getType() : string;

    public function __construct(
        private readonly AuthorizationCheckerInterface $securityContext,
        private readonly PageFieldDataMapper $pageFieldDataMapper,
        private readonly ParameterBagInterface $parameterBag
    ){
        $entities = $this->parameterBag->get('aropixel_page.entities');
        $this->pageClass = $entities[PageInterface::class];
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $type = $builder->getData()->getType();

        if ($builder->getData() && $type == 'default') {
            $builder
                ->add('title', TextType::class, ['label'  => 'Titre de la page'])
                ->add('slug', TextType::class, ['label'  => 'Permalien', 'required' => false])
            ;
        }

        if ($builder->getData() && $type == 'default_translatable') {

            $builder
                ->add('title', TranslatableType::class, [
                    'label'                => 'Titre de la page',
                    'personal_translation' => PageTranslation::class,
                    'property_path'        => 'translations'
                ])
                ->add('slug', TranslatableType::class, [
                    'label'                => 'Permalien',
                    'personal_translation' => PageTranslation::class,
                    'property_path'        => 'translations',
                    'required' => false
                ])
            ;

        }

        if ($type == 'default_translatable') {

            $builder
                ->add('metaTitle', TranslatableType::class, [
                    'label' => 'Meta title',
                    'personal_translation' => PageTranslation::class,
                    'property_path' => 'translations',
                    'required' => false
                ])
                ->add('metaDescription', TranslatableType::class, [
                    'label' => 'Meta description',
                    'personal_translation' => PageTranslation::class,
                    'property_path' => 'translations',
                    'required' => false
                ])
                ->add('metaKeywords', TranslatableType::class, [
                    'label' => 'Meta keywords',
                    'personal_translation' => PageTranslation::class,
                    'property_path' => 'translations',
                    'required' => false
                ])
            ;

        } else {

            // Si le titre n'est pas autorisé et qu'on est pas super admin,
            $builder
                ->add('metaTitle', null, ['label' => 'Meta title'])
                ->add('metaDescription', null, ['label' => 'Meta description'])
                ->add('metaKeywords', null, ['label' => 'Meta keywords']);

        }

        $builder
            ->setDataMapper($this->pageFieldDataMapper)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->pageClass,
            'multiple' => false,
        ]);
    }

}
