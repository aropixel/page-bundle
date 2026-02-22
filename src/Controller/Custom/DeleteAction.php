<?php

namespace Aropixel\PageBundle\Controller\Custom;

use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Delete a custom page.
 */
#[IsGranted('ROLE_CONTENT_EDITOR')]
class DeleteAction extends AbstractController
{
    /**
     * @param PageRepository $pageRepository
     */
    public function __construct(
        private readonly PageRepository $pageRepository,
    ) {
    }

    #[Route("/page-builder/{id}/delete", name: "aropixel_custom_page_delete", methods: ['DELETE', 'POST'], requirements: ['id' => '\d+'])]
    public function __invoke(Page $page, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete__page' . $page->getId(), $request->request->get('_token'))) {
            $this->pageRepository->remove($page, true);
            $this->addFlash('notice', 'La page a bien été supprimée.');
        }

        return $this->redirectToRoute('aropixel_custom_page_list');
    }
}
