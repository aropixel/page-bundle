<?php

namespace Aropixel\PageBundle\Component\Builder;

/**
 * Renders a block type the bundle does not know about.
 *
 * Declaring a block under `page_builder.custom_blocks` and implementing its JavaScript makes it
 * editable — it appears in the library, has a preview and an inspector, and is stored in the page's
 * JSON. None of that renders it: the built-in renderers only know their own types. Implement this
 * to give a custom block its HTML; without it the block is silently dropped from the output.
 *
 * Implementations are autoconfigured — no tag to declare.
 */
interface CustomBlockRendererInterface
{
    public function supports(string $type): bool;

    /**
     * @param array<string, mixed> $block The block payload, as stored in the page's JSON
     *
     * @return string HTML. The implementation owns its escaping.
     */
    public function render(array $block): string;
}
