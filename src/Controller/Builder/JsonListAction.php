<?php

namespace Aropixel\PageBundle\Controller\Builder;

use Aropixel\PageBundle\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Get builder pages as JSON.
 */
class JsonListAction extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%aropixel_page.page_builder.enabled%')]
        private readonly bool $pageBuilderEnabled = true,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        if (!$this->pageBuilderEnabled) {
            throw $this->createNotFoundException();
        }

        $pages = $this->entityManager->getRepository(Page::class)->findBuilderPages();

        $data = array_map(fn (Page $page) => [
            'slug' => $page->getSlug(),
            'title' => $page->getTitle(),
        ], $pages);

        return new JsonResponse($data);
    }
}
