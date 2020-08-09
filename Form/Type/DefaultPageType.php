<?php

namespace Aropixel\PageBundle\Form\Type;

use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
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

        $builder
            ->add('excerpt', null, array('label'  => 'Chapeau'))
            ->add('description', null, array('label'  => 'Description', 'attr' => array('class' => 'ckeditor')))
            ->add('image', ImageType::class)
        ;

    }

    public function getType(): string
    {
        return 'default';
    }


}
