<?php

namespace Aropixel\PageBundle\Form\BlockInput;


use Aropixel\PageBundle\Block\BlockManager;
use Aropixel\PageBundle\Entity\Block;
use Aropixel\PageBundle\Entity\BlockInput;
use Aropixel\PageBundle\Form\BlockInputType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockInputTabsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('content', CollectionType::class, [
            'entry_type' => BlockInputTabsContentType::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'allow_add'    => true,
            'allow_delete' => true,
            'by_reference' => false,
        ]);

    }

}
