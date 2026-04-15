<?php

namespace Aropixel\PageBundle\Controller\Custom;

use Aropixel\PageBundle\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Custom page builder editor.
 */
class BuilderAction extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly array $pageBuilderConfig = [],
    ) {
    }

    public function __invoke(Request $request, ?Page $page = null): Response
    {
        if ($page && method_exists($page, 'setTranslatableLocale')) {
            $page->setTranslatableLocale($request->getLocale());
            $this->entityManager->refresh($page);
        }

        $allPages = $page ? $this->entityManager->getRepository(Page::class)->findOthers($page) : $this->entityManager->getRepository(Page::class)->findAll();

        return $this->render('@AropixelPage/custom/index.html.twig', [
            'page' => $page,
            'page_builder_config' => $this->pageBuilderConfig,
            'allPages' => $allPages,
        ]);
    }
}
