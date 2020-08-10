<?php

namespace Aropixel\PageBundle\Form\Type;

use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use Aropixel\PageBundle\Entity\Field;
use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Entity\PageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


class DefaultPageType extends AbstractPageType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('excerpt', null, array('label'  => 'Chapeau'))
            ->add('description', TextareaType::class, array('label'  => 'Description', 'attr' => array('class' => 'ckeditor')))
            ->add('image', ImageType::class, [
                'data_class' => Field::class,
                'data_value' => 'value',
                'library' => self::class
            ])
            ->add('status', ChoiceType::class, array(
                'choices'  => array(
                    'Oui' => 'online',
                    'Non' => 'offline',
                ),
                'expanded' => true
            ))
            ->add('createdAt', DateTimeType::class, array(
                'required' => false,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd',
            ))
            ->add('publishAt', null, array(
                'required' => false,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd',
                'years' => range(date('Y') - 50, date('Y') + 50),
            ))
            ->add('publishUntil', null, array(
                'required' => false,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd',
                'years' => range(date('Y') - 50, date('Y') + 50),
            ))
        ;

    }

    public function getType(): string
    {
        return 'default';
    }


}
