<?php

namespace Aropixel\PageBundle\Http\Action\Page;

use Aropixel\PageBundle\Http\Form\Page\FormFactory;
use Aropixel\PageBundle\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class IndexPageAction extends AbstractController
{
    private FormFactory $formFactory;
    private PageRepository $pageRepository;


    public function __invoke(string $type) : Response
    {
        $pages = $this->pageRepository->findBy(['type' => $type], ['title' => 'ASC']);

        $delete_forms = array();
        foreach ($pages as $page) {
            $deleteForm = $this->formFactory->createDeleteForm($page);
            $delete_forms[$page->getId()] = $deleteForm->createView();
        }

        return $this->render('@AropixelPage/index.html.twig', [
            'type' => $type,
            'pages' => $pages,
            'delete_forms' => $delete_forms,
        ]);
    }

}