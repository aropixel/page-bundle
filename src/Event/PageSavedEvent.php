<?php

namespace Aropixel\PageBundle\Event;

use Aropixel\PageBundle\Entity\Page;
use Symfony\Contracts\EventDispatcher\Event;

class PageSavedEvent extends Event
{
    public const NAME = 'aropixel.page.saved';

    public function __construct(
        private readonly Page $page,
        private readonly string $locale,
        private readonly string $renderedHtml,
    ) {
    }

    public function getPage(): Page
    {
        return $this->page;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getRenderedHtml(): string
    {
        return $this->renderedHtml;
    }
}
