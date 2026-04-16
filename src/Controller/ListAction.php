<?php

namespace Aropixel\PageBundle\Controller;

use Aropixel\PageBundle\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the listing of pages of a specific type.
 */
class ListAction extends AbstractController
{
    public function __construct(
        private readonly PageRepository $pageRepository,
        #[Autowire('%aropixel_page.page_builder.enabled%')]
        private readonly bool $pageBuilderEnabled,
    ) {
    }

    public function __invoke(): Response
    {
        $pages = $this->pageRepository->findBy([], ['title' => 'ASC']);

        return $this->render('@AropixelPage/index.html.twig', [
            'pages' => $pages,
            'page_builder_enabled' => $this->pageBuilderEnabled,
        ]);
    }
}
