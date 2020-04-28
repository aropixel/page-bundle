<?php

namespace Aropixel\PageBundle\Form\BlockInput;


use Aropixel\PageBundle\Entity\BlockInput;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockInputTextareaType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('content', TextareaType::class, [
            'label' => false,
            'attr' => [
                'class' => 'ckeditor'
            ]
        ]);
    }
}
