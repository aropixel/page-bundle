<?php

namespace Aropixel\PageBundle\Controller\DefaultPage;

use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the listing of pages of a specific type.
 */
class IndexPageAction extends AbstractController
{
    /**
     * @param string $type The page type to filter by.
     * @return Response
     */
    public function __invoke(string $type) : Response
    {
        $pages = $this->pageRepository->findBy(['type' => Page::TYPE_DEFAULT], ['title' => 'ASC']);

        return $this->render('@AropixelPage/index.html.twig', [
            'type' => $type,
            'pages' => $pages
        ]);
    }

}