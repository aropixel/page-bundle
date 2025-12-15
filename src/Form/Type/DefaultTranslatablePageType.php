<?php

namespace Aropixel\PageBundle\Form\Type;

use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use Aropixel\AdminBundle\Form\Type\TranslatableType;
use Aropixel\PageBundle\Entity\FieldInterface;
use Aropixel\PageBundle\Entity\PageTranslation;
use Aropixel\PageBundle\Form\DataMapper\PageFieldDataMapper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


class DefaultTranslatablePageType extends AbstractPageType
{

    public function __construct(
        PageFieldDataMapper $pageFieldDataMapper,
        ParameterBagInterface $parameterBag,
        private array $entities = [],
    ) {
        parent::__construct($pageFieldDataMapper, $parameterBag);
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fieldClass = $this->entities[FieldInterface::class];

        parent::buildForm($builder, $options);

        $builder
            ->add('excerpt', TranslatableType::class, [
                'label'                => 'Chapeau',
                'personal_translation' => PageTranslation::class,
                'property_path'        => 'translations'
            ])
            ->add('description', TranslatableType::class, [
                'label'                => 'Description',
                'personal_translation' => PageTranslation::class,
                'property_path'        => 'translations',
                'widget' => TextareaType::class,
                'attr' => ['class' => 'ckeditor']
            ])
            ->add('image', ImageType::class, [
                'label' => 'Image principale',
                'data_class' => $fieldClass,
                'data_value' => 'value',
                'library' => self::class
            ])
            ->add('status', HiddenType::class)
            ->add('createdAt', DateTimeType::class, [
                'label' => "Créé le",
                'required' => false,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd',
            ])
            ->add('publishAt', null, [
                'label' => "Publié le",
                'required' => false,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd',
                'years' => range(date('Y') - 50, date('Y') + 50),
            ])
            ->add('publishUntil', null, [
                'label' => "Jusqu'au",
                'required' => false,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd',
                'years' => range(date('Y') - 50, date('Y') + 50),
            ])
        ;

    }

    public function getType(): string
    {
        return 'default_translatable';
    }


}
