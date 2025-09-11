<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 05/07/2020 à 16:46
 */

namespace Aropixel\PageBundle\Form;


use Aropixel\PageBundle\Entity\PageInterface;
use Aropixel\PageBundle\Form\Type\PageFormTypeInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormFactoryInterface as SfFormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class FormFactory implements FormFactoryInterface
{

    private $forms = [];

    public function __construct(
        private readonly SfFormFactoryInterface $sfFormFactory,
        private readonly ParameterBagInterface $parameterBag,
    )
    {}

    public function addForm(PageFormTypeInterface $formType)
    {
        $type = $formType->getType();

        if (array_key_exists($type, $this->forms)) {
            throw new \Exception(sprintf('The page form with "%s" type has already been declared by "%s"', $type, $this->forms[$type]::class));
        }

        $this->forms[$type] = $formType;
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     */
    public function createForm(PageInterface $page, array $options = []): FormInterface
    {
        foreach ($this->forms as $type => $formType) {
            if ($page->getType() == $type) {
                return $this->sfFormFactory->create($formType::class, $page, $options);
            }
        }

        throw new \Exception(sprintf('The page form with "%s" type couldn\'t be not found', $page->getType()));
    }

    public function getTemplatePath() : string
    {
        $formsConfig = $this->parameterBag->get('aropixel_page.forms');
        return $formsConfig['template_path'];
    }

}
