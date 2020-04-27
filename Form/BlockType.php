<?php

namespace Aropixel\PageBundle\Form;


use Aropixel\PageBundle\Block\BlockManager;
use Aropixel\PageBundle\Entity\Block;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockType extends AbstractType
{

    /**
     * @var BlockManager
     */
    private $blockManager;

    public function __construct(BlockManager $blockManager)
    {
        $this->blockManager = $blockManager;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('inputs', CollectionType::class, [
            'entry_type' => BlockInputType::class,
            'entry_options' => ['label' => false],
            'label' => false
        ]);


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Block::class,
        ]);
    }
}
