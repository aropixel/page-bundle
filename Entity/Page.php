<?php

namespace Aropixel\PageBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Page
 */
class Page
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $excerpt;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $metaTitle;

    /**
     * @var string
     */
    private $metaDescription;

    /**
     * @var string
     */
    private $metaKeywords;

    /**
     * @var boolean
     */
    private $isPageTitleEnabled;

    /**
     * @var boolean
     */
    private $isPageExcerptEnabled;

    /**
     * @var boolean
     */
    private $isPageDescriptionEnabled;

    /**
     * @var boolean
     */
    private $isPageImageEnabled;

    /**
     * @var boolean
     */
    private $isPageGalleryEnabled;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var \DateTime
     */
    private $publishAt;

    /**
     * @var \DateTime
     */
    private $publishUntil;

    /**
     * @var PageImage
     */
    private $image;

    /**
     * @var PageGallery[]
     */
    private $gallery;



    public function __construct()
    {
        $this->gallery = new ArrayCollection();
    }




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

    public function getIsPageGalleryEnabled(): ?bool
    {
        return $this->isPageGalleryEnabled;
    }

    public function setIsPageGalleryEnabled(bool $isPageGalleryEnabled): self
    {
        $this->isPageGalleryEnabled = $isPageGalleryEnabled;

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

    /**
     * @return Collection|PageGallery[]
     */
    public function getGallery(): Collection
    {
        return $this->gallery;
    }

    public function addGallery(PageGallery $gallery): self
    {
        if (!$this->gallery->contains($gallery)) {
            $this->gallery[] = $gallery;
            $gallery->setPage($this);
        }

        return $this;
    }

    public function removeGallery(PageGallery $gallery): self
    {
        if ($this->gallery->contains($gallery)) {
            $this->gallery->removeElement($gallery);
            // set the owning side to null (unless already changed)
            if ($gallery->getPage() === $this) {
                $gallery->setPage(null);
            }
        }

        return $this;
    }


}
