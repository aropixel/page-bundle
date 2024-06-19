<?php

namespace Aropixel\PageBundle\Http\Action\Page;

use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DeletePageAction extends AbstractController
{
    public function __construct(
        private readonly PageRepository $pageRepository,
    )
    {}

    public function __invoke(Request $request, Page $page) : Response
    {

        $type = $page->getType();
        $title = $page->getTitle();

        if ($this->isCsrfTokenValid('delete__' . $page->getType() . '_page' . $page->getId(), $request->request->get('_token'))) {
            $this->pageRepository->remove($page, true);
            $this->addFlash('notice', 'La page "' . $title . '" a bien été supprimée.');
        }

        return $this->redirectToRoute('aropixel_page_index', ['type' => $type]);

    }
}