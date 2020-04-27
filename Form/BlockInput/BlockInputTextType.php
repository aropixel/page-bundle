<?php

namespace Aropixel\PageBundle\Form\BlockInput;


use Aropixel\PageBundle\Block\BlockManager;
use Aropixel\PageBundle\Entity\Block;
use Aropixel\PageBundle\Entity\BlockInput;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockInputTextType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('content', TextType::class, [
            'label' => false
        ]);
    }

}
