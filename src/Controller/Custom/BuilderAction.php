<?php

namespace Aropixel\PageBundle\Controller\Custom;

use Aropixel\PageBundle\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Custom page builder editor.
 */
class BuilderAction extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
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

        $config = array_merge($this->pageBuilderConfig, [
            'translations' => $this->buildTranslations(),
        ]);

        return $this->render('@AropixelPage/custom/index.html.twig', [
            'page' => $page,
            'page_builder_config' => $config,
            'allPages' => $allPages,
        ]);
    }

    /** @return array<string, string> */
    private function buildTranslations(): array
    {
        $keys = [
            'form.choose',
            'form.save',
            'page.builder.block_title.image',
            'page.builder.block_title.button',
            'page.builder.block_title.banner',
            'page.builder.block_title.blog',
            'page.builder.block_title.divider',
            'page.builder.block_title.nested_row',
            'page.builder.block.image.alt',
            'page.builder.block.image.alt_placeholder',
            'page.builder.block.image.original_size',
            'page.builder.block.image.size',
            'page.builder.block.button.label_field',
            'page.builder.block.button.link_page',
            'page.builder.block.button.link_page_label',
            'page.builder.block.button.color',
            'page.builder.block.button.default_label',
            'page.builder.block.banner.default_label',
            'page.builder.block.blog.default_label',
            'page.builder.block.divider.margin_top',
            'page.builder.block.divider.margin_bottom',
            'page.builder.block.slider.delete_confirm',
            'page.builder.block.slider.empty',
            'page.builder.text.toolbar.bold',
            'page.builder.text.toolbar.italic',
            'page.builder.text.toolbar.underline',
            'page.builder.text.toolbar.link',
            'page.builder.text.toolbar.link_prompt',
            'page.builder.text.toolbar.list_unordered',
            'page.builder.text.toolbar.list_ordered',
            'page.builder.text.toolbar.align_left',
            'page.builder.text.toolbar.align_center',
            'page.builder.text.toolbar.align_right',
            'page.builder.inspector.column.horizontal_alignment',
            'page.builder.inspector.column.link_type',
            'page.builder.inspector.column.link_url',
            'page.builder.canvas.section.insert_before',
            'page.builder.canvas.section.insert_after',
            'page.builder.canvas.move_up',
            'page.builder.canvas.move_down',
            'page.builder.canvas.move_left',
            'page.builder.canvas.move_right',
            'page.builder.canvas.section.duplicate',
            'page.builder.canvas.section.delete',
            'page.builder.canvas.section.delete_confirm',
            'page.builder.canvas.row.duplicate',
            'page.builder.canvas.row.delete',
            'page.builder.canvas.row.delete_confirm',
            'page.builder.canvas.column.duplicate',
            'page.builder.canvas.column.delete',
            'page.builder.canvas.column.delete_confirm',
            'page.builder.canvas.block.duplicate',
            'page.builder.canvas.block.delete',
            'page.builder.canvas.block.delete_confirm',
            'page.builder.canvas.column.quick_add_icon_box',
            'page.builder.block.title',
            'page.builder.block.text',
            'page.builder.block.button',
            'page.builder.block.image',
            'page.builder.notification.close',
            'page.builder.saver.title_required',
            'page.builder.saver.success',
            'page.builder.saver.error',
            'page.builder.saver.error_network',
        ];

        $translations = [];
        foreach ($keys as $key) {
            $translations[$key] = $this->translator->trans($key);
        }

        return $translations;
    }
}
