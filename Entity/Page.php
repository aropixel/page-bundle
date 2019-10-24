<?php

namespace Aropixel\PageBundle\Entity;

use Aropixel\AdminBundle\Entity\Publishable;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Page
 */
class Page
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $status = Publishable::STATUS_OFFLINE;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $excerpt;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $metaTitle;

    /**
     * @var string
     */
    protected $metaDescription;

    /**
     * @var string
     */
    protected $metaKeywords;

    /**
     * @var boolean
     */
    protected $isPageTitleEnabled = true;

    /**
     * @var boolean
     */
    protected $isPageExcerptEnabled = true;

    /**
     * @var boolean
     */
    protected $isPageDescriptionEnabled = true;

    /**
     * @var boolean
     */
    protected $isPageImageEnabled = true;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     */
    protected $publishAt;

    /**
     * @var \DateTime
     */
    protected $publishUntil;

    /**
     * @var PageImage
     */
    protected $image;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): self
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(?string $metaKeywords): self
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    public function getIsPageTitleEnabled(): ?bool
    {
        return $this->isPageTitleEnabled;
    }

    public function setIsPageTitleEnabled(bool $isPageTitleEnabled): self
    {
        $this->isPageTitleEnabled = $isPageTitleEnabled;

        return $this;
    }

    public function getIsPageExcerptEnabled(): ?bool
    {
        return $this->isPageExcerptEnabled;
    }

    public function setIsPageExcerptEnabled(bool $isPageExcerptEnabled): self
    {
        $this->isPageExcerptEnabled = $isPageExcerptEnabled;

        return $this;
    }

    public function getIsPageDescriptionEnabled(): ?bool
    {
        return $this->isPageDescriptionEnabled;
    }

    public function setIsPageDescriptionEnabled(bool $isPageDescriptionEnabled): self
    {
        $this->isPageDescriptionEnabled = $isPageDescriptionEnabled;

        return $this;
    }

    public function getIsPageImageEnabled(): ?bool
    {
        return $this->isPageImageEnabled;
    }

    public function setIsPageImageEnabled(bool $isPageImageEnabled): self
    {
        $this->isPageImageEnabled = $isPageImageEnabled;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPublishAt(): ?\DateTimeInterface
    {
        return $this->publishAt;
    }

    public function setPublishAt(?\DateTimeInterface $publishAt): self
    {
        $this->publishAt = $publishAt;

        return $this;
    }

    public function getPublishUntil(): ?\DateTimeInterface
    {
        return $this->publishUntil;
    }

    public function setPublishUntil(?\DateTimeInterface $publishUntil): self
    {
        $this->publishUntil = $publishUntil;

        return $this;
    }

    public function getImage(): ?PageImage
    {
        return $this->image;
    }

    public function setImage(?PageImage $image): self
    {
        if ($image->getImage()) {
            $this->image = $image;
            $this->image->setPage($this);
        }

        return $this;
    }


}
