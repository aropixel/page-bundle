<?php

namespace Aropixel\PageBundle\Entity;

/**
 * Interface for Page entities.
 */
interface PageInterface
{
    public function getId(): ?int;

    public function getType(): string;

    public function setType(string $type): self;

    public function getStaticCode(): ?string;

    public function setStaticCode(?string $staticCode): self;

    public function isDeletable(): bool;

    public function setIsDeletable(bool $isDeletable): self;

    public function getTitle(): ?string;

    public function setTitle(string $title): self;
}
