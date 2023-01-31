<?php

namespace Aropixel\PageBundle\Http\Action\Page;

use Aropixel\PageBundle\Http\Form\Page\FormFactory;
use Aropixel\PageBundle\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class DeletePageAction extends AbstractController
{
    public function __construct(
        private readonly FormFactory $formFactory,
        private readonly PageRepository $pageRepository,
        private readonly RequestStack $request,
    )
    {}

    public function __invoke(int $id) : Response
    {
        $page = $this->pageRepository->find($id);
        $type = $page->getType();
        $title = $page->getTitle();

        $form = $this->formFactory->createDeleteForm($page);
        $form->handleRequest($this->request->getMainRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->pageRepository->remove($page, true);

            $this->addFlash('notice', 'La page "'.$title.'" a bien été supprimé.');
        }

        return $this->redirectToRoute('aropixel_page_index', ['type' => $type]);

    }
}