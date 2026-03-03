<?php

namespace Aropixel\PageBundle\Entity;

use Aropixel\AdminBundle\Entity\Publishable;
use Aropixel\AdminBundle\Entity\PublishableTrait;
use Aropixel\AdminBundle\Entity\TranslatableTrait;
use Aropixel\PageBundle\Repository\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Mapping\Annotation\Slug;
use Gedmo\Translatable\Translatable;

/**
 * Base Page entity.
 *
 * This entity supports both standard HTML content and custom JSON content
 * for future page builder integration.
 */
#[ORM\MappedSuperclass(repositoryClass: PageRepository::class)]
#[ORM\Table(name: 'aropixel_page')]
#[ORM\Index(columns: ['type'])]
#[Gedmo\TranslationEntity(class: PageTranslation::class)]
class Page implements PageInterface, Translatable
{
    use PublishableTrait;
    use TranslatableTrait;

    /**
     * Standard page type with HTML content.
     */
    public const TYPE_DEFAULT = 'default';

    /**
     * Custom page type with JSON content (e.g., for page builders).
     */
    public const TYPE_CUSTOM = 'custom';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    /**
     * Publication status of the page.
     */
    #[ORM\Column(type: 'string', length: 20)]
    protected string $status = Publishable::STATUS_OFFLINE;

    /**
     * The type of page (default or custom).
     */
    #[ORM\Column(type: 'string', length: 100)]
    protected string $type;

    /**
     * Page title (translatable).
     */
    #[ORM\Column(type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    protected ?string $title = null;

    /**
     * Unique identifier for system pages.
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true, unique: true)]
    protected ?string $staticCode = null;

    /**
     * Whether the page can be deleted through the admin UI.
     */
    #[ORM\Column(type: 'boolean')]
    protected bool $isDeletable = true;

    /**
     * Short summary or excerpt (translatable).
     */
    #[ORM\Column(type: 'text', nullable: true)]
    #[Gedmo\Translatable]
    protected ?string $excerpt = null;

    /**
     * Standard HTML content (translatable).
     */
    #[ORM\Column(type: 'text', nullable: true)]
    #[Gedmo\Translatable]
    protected ?string $htmlContent = null;

    /**
     * Structured JSON content for custom page builders (translatable).
     */
    #[ORM\Column(type: 'text', nullable: true)]
    #[Gedmo\Translatable]
    protected ?string $jsonContent = null;

    /**
     * URL-friendly identifier (translatable).
     */
    #[ORM\Column(type: 'string')]
    #[Gedmo\Translatable]
    #[Slug(fields: ['title'])]
    protected ?string $slug = null;

    /**
     * SEO Meta Title (translatable).
     */
    #[ORM\Column(type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    protected ?string $metaTitle = null;

    /**
     * SEO Meta Description (translatable).
     */
    #[ORM\Column(type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    protected ?string $metaDescription = null;

    /**
     * SEO Meta Keywords (translatable).
     */
    #[ORM\Column(type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    protected ?string $metaKeywords = null;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
    protected ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $publishAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $publishUntil = null;

    /**
     * @var ?Collection<int,PageTranslation>
     */
    #[ORM\OneToMany(mappedBy: 'object', targetEntity: PageTranslation::class, cascade: ['persist', 'remove'])]
    protected ?Collection $translations = null;

    #[Gedmo\Locale]
    protected ?string $locale = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStaticCode(): ?string
    {
        return $this->staticCode;
    }

    /**
     * @param string|null $staticCode
     * @return self
     */
    public function setStaticCode(?string $staticCode): self
    {
        $this->staticCode = $staticCode;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeletable(): bool
    {
        return $this->isDeletable;
    }

    /**
     * @param bool $isDeletable
     * @return self
     */
    public function setIsDeletable(bool $isDeletable): self
    {
        $this->isDeletable = $isDeletable;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->getTranslation('title');
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->getTranslation('slug');
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getExcerpt(): ?string
    {
        return $this->getTranslation('excerpt');
    }

    public function setExcerpt(string $excerpt): self
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    public function getHtmlContent(): ?string
    {
        return $this->getTranslation('htmlContent');
    }

    public function setHtmlContent(?string $htmlContent): static
    {
        $this->htmlContent = $htmlContent;

        return $this;
    }

    public function getJsonContent(): ?string
    {
        return $this->getTranslation('jsonContent');
    }

    public function setJsonContent(?string $jsonContent): static
    {
        $this->jsonContent = $jsonContent;

        return $this;
    }

    public function getMetaTitle(): ?string
    {
        return $this->getTranslation('metaTitle');
    }

    public function setMetaTitle(?string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->getTranslation('metaDescription');
    }

    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getMetaKeywords(): ?string
    {
        return $this->getTranslation('metaKeywords');
    }

    public function setMetaKeywords(?string $metaKeywords): self
    {
        $this->metaKeywords = $metaKeywords;

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
}
