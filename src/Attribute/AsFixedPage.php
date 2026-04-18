<?php

namespace Aropixel\PageBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AsFixedPage
{
    public function __construct(
        public readonly string $code,
        public readonly string $title,
        public readonly string $type = 'default',
        public readonly bool $deletable = false,
    ) {
    }
}
