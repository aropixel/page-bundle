<?php

namespace Aropixel\PageBundle\Entity;

use Aropixel\AdminBundle\Entity\Publishable;
use Aropixel\AdminBundle\Entity\PublishableTrait;
use Aropixel\AdminBundle\Entity\TranslatableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Mapping\Annotation\Slug;
use Gedmo\Translatable\Translatable;
use Symfony\Component\PropertyAccess\PropertyAccess;

#[Gedmo\TranslationEntity(class: PageTranslation::class)]
class Page implements PageInterface, Translatable
{
    const TYPE_DEFAULT = 'default';
    const TYPE_DEFAULT_TRANSLATABLE = 'default_translatable';

    protected ?int $id = null;
    protected string $status = Publishable::STATUS_OFFLINE;
    protected string $type;
    protected string $code;

    #[Gedmo\Translatable]
    protected ?string $title = null;

    #[Gedmo\Translatable]
    protected ?string $excerpt = null;

    #[Gedmo\Translatable]
    protected ?string $description = null;

    #[Gedmo\Translatable]
    #[Slug(fields: ['title'])]
    protected ?string $slug = null;

    #[Gedmo\Translatable]
    protected ?string $metaTitle = null;

    #[Gedmo\Translatable]
    protected ?string $metaDescription = null;

    #[Gedmo\Translatable]
    protected ?string $metaKeywords = null;

    protected ?\DateTimeInterface $createdAt = null;
    protected ?\DateTimeInterface $updatedAt = null;
    protected ?\DateTimeInterface $publishAt = null;
    protected ?\DateTimeInterface $publishUntil = null;
    protected ?iterable $fields = null;
    protected ?array $fieldValues = null;

    use PublishableTrait;
    use TranslatableTrait;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }


    private function compileFieldsValues()
    {
        $this->fieldValues = [];
        foreach ($this->fields as $field) {

            $value = $field->getExplodedValue();
            $this->fieldValues = array_replace_recursive($this->fieldValues, $value);
            $this->ksortTree($this->fieldValues);

        }

    }

    function ksortTree( &$array )
    {
        if (!is_array($array)) {
            return false;
        }

        ksort($array);
        foreach ($array as $k=>$v) {
            $this->ksortTree($array[$k]);
        }
        return true;
    }


    public function getField($key)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        try {
            return $propertyAccessor->getValue($this, $key);
        }
        catch (\Exception $e) {

            if (is_null($this->fieldValues)) {
                $this->compileFieldsValues();
            }

            return (array_key_exists($key, $this->fieldValues) ? $this->fieldValues[$key] : null);

        }

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

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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

    public function getDescription(): ?string
    {
        return $this->getTranslation('description');
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getFields()
    {
        return $this->fields;
    }

    public function addField(?FieldInterface $field = null)
    {
        $this->fields->add($field);

        $field->setPage($this);
    }

    public function removeField(FieldInterface $field)
    {
        $this->fields->removeElement($field);
        $field->setPage(null);
    }

}
