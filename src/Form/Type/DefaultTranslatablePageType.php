<?php

namespace Aropixel\PageBundle\Form\Type;

use Aropixel\AdminBundle\Form\Type\TranslatableType;
use Aropixel\PageBundle\Entity\PageTranslation;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;


/**
 * Form type for the default page with translatable HTML content.
 */
class DefaultTranslatablePageType extends AbstractPageType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('excerpt', TranslatableType::class, [
                'label'                => 'Chapeau',
                'personal_translation' => PageTranslation::class,
                'property_path'        => 'translations'
            ])
            ->add('htmlContent', TranslatableType::class, [
                'label'                => 'Contenu',
                'personal_translation' => PageTranslation::class,
                'property_path'        => 'translations',
                'widget' => TextareaType::class,
                'attr' => ['class' => 'ckeditor']
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
