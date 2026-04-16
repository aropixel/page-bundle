<?php

namespace Aropixel\PageBundle\Component\Builder;

interface PageBuilderRendererInterface
{
    /**
     * Convert a page-builder JSON payload into a complete HTML fragment.
     *
     * @param array|string|null $content Either the raw JSON string, the decoded
     *                                   array ({sections:[...]}) or null/empty.
     */
    public function render(array|string|null $content): string;
}
