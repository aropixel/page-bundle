<?php

namespace Aropixel\PageBundle\Entity;

/**
 * Lightweight data holder used by AttachAction (image widget rendering).
 *
 * The image in a page builder block is stored as JSON (filename + URL) directly
 * in the block data, not as a Doctrine entity. This class only serves as a
 * temporary container so that ImageType can render the widget HTML in "file name
 * mode" (data_value = "value").
 */
class PageBlockImageData
{
    private ?string $value = null;

    /** @var array<string, mixed>|null */
    private ?array $attributes = null;

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /** @return array<string, mixed>|null */
    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    /** @param array<string, mixed>|null $attributes */
    public function setAttributes(?array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }
}
