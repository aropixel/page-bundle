<?php

namespace Aropixel\PageBundle\Controller;

use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the listing of pages of a specific type.
 */
class ListAction extends AbstractController
{
    public function __construct(
        private readonly PageRepository $pageRepository,
    ) {
    }

    /**
     * @param string $type The page type to filter by.
     * @return Response
     */
    public function __invoke() : Response
    {
        $pages = $this->pageRepository->findBy([], ['title' => 'ASC']);

        return $this->render('@AropixelPage/index.html.twig', [
            'pages' => $pages
        ]);
    }

}