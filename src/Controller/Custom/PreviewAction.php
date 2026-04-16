<?php

namespace Aropixel\PageBundle\Controller\Custom;

use Aropixel\PageBundle\Component\Builder\PageBuilderRendererInterface;
use Aropixel\PageBundle\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Renders a standalone preview of a custom page using the configured renderer.
 */
class PreviewAction extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PageBuilderRendererInterface $renderer,
        private readonly array $pageBuilderConfig = [],
    ) {
    }

    public function __invoke(int $id): Response
    {
        $page = $this->entityManager->getRepository(Page::class)->find($id);

        if (!$page) {
            throw $this->createNotFoundException(sprintf('Page #%d introuvable.', $id));
        }

        $renderedContent = $this->renderer->render($page->getJsonContent());

        return $this->render('@AropixelPage/custom/preview.html.twig', [
            'page' => $page,
            'rendered_content' => $renderedContent,
            'renderer' => $this->pageBuilderConfig['renderer'] ?? 'uikit',
            'custom_css' => $this->pageBuilderConfig['custom_css'] ?? null,
        ]);
    }
}
