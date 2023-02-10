<?php

namespace Aropixel\PageBundle\Http\Form\Page;

use Aropixel\PageBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;

class FormFactory extends AbstractController
{
    /**
     * Creates a form to delete a Page entity by id.
     */
    public function createDeleteForm(Page $page) : FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('aropixel_page_delete', array('id' => $page->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }

    public function getTemplatePath()
    {
        $formsConfig = $this->getParameter('aropixel_page.forms');
        return $formsConfig['template_path'];
    }



}