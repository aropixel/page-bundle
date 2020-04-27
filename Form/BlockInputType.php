<?php

namespace Aropixel\PageBundle\Form;


use Aropixel\PageBundle\Block\BlockManager;
use Aropixel\PageBundle\Entity\Block;
use Aropixel\PageBundle\Entity\BlockInput;
use Aropixel\PageBundle\Form\BlockInput\BlockInputTextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockInputType extends AbstractType
{

    private const BLOCK_INPUT_TYPE_NS = '\Aropixel\PageBundle\Form\BlockInput\\';
    private const BLOCK_INPUT_TYPE_POSTFIX = 'Type';
    private const BLOCK_INPUT_TYPE_PREFIX = 'BlockInput';

    /**
     * @var BlockManager
     */
    private $blockManager;

    public function __construct(BlockManager $blockManager)
    {
        $this->blockManager = $blockManager;
    }

    /**
     * @param $inputConfig
     *
     * @return string
     */
    private function getFqnInputClassType( $inputConfig ): string
    {
        $type           = ucfirst( $inputConfig['type'] );
        $typeClass      = self::BLOCK_INPUT_TYPE_PREFIX.$type.self::BLOCK_INPUT_TYPE_POSTFIX;
        $classNamespace = self::BLOCK_INPUT_TYPE_NS;
        $fqnInputClassType   = $classNamespace . $typeClass;

        return $fqnInputClassType;

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($builder){
                $form = $event->getForm();
                $input = $event->getData();

                if ($input instanceof BlockInput) {

                    $inputCode = $input->getCode();
                    $blockCode = $input->getBlock()->getCode();

                    $inputConfig = $this->blockManager->getConfiguredBlockInput($inputCode, $blockCode );

                    $fqnInputClassType = $this->getFqnInputClassType( $inputConfig );

                    // widget de chaque 'form.children.inputs'
                    $form->add('content', $fqnInputClassType, [
                        'label' => $inputConfig['label'],
                    ]);
                }
            }
        );

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BlockInput::class,
        ]);
    }
}
