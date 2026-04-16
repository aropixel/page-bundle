<?php

namespace Aropixel\PageBundle\Controller\Custom;

use Aropixel\PageBundle\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Get custom pages as JSON.
 */
class JsonListAction extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%aropixel_page.page_builder.enabled%')]
        private readonly bool $pageBuilderEnabled = true,
    ) {
    }

    #[Route('/page-builder/json/list', name: 'aropixel_custom_page_json_list', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        if (!$this->pageBuilderEnabled) {
            throw $this->createNotFoundException();
        }

        $pages = $this->entityManager->getRepository(Page::class)->findCustomPages();

        $data = array_map(fn (Page $page) => [
            'slug' => $page->getSlug(),
            'title' => $page->getTitle(),
        ], $pages);

        return new JsonResponse($data);
    }
}
