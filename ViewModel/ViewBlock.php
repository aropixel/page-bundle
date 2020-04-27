<?php

declare(strict_types=1);

namespace Aropixel\PageBundle\ViewModel;

class ViewBlock
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $pageName;

    /**
     * @var bool
     */
    private $isPersisted;

    /**
     * @var string
     */
    private $code;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName( string $name ): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPageName(): ?string
    {
        return $this->pageName;
    }

    /**
     * @param string $page
     */
    public function setPageName( string $pageName ): void
    {
        $this->pageName = $pageName;
    }

    /**
     * @param bool $isPersisted
     */
    public function setIsPersisted( bool $isPersisted ): void
    {
        $this->isPersisted = $isPersisted;
    }

    /**
     * @return bool
     */
    public function isPersisted(): bool
    {
        return $this->isPersisted;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode( string $code ): void
    {
        $this->code = $code;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId( int $id ): void
    {
        $this->id = $id;
    }


}
