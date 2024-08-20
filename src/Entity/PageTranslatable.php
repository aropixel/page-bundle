<?php

namespace Aropixel\PageBundle\Entity;

use Aropixel\AdminBundle\Entity\Publishable;
use Aropixel\AdminBundle\Entity\PublishableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Mapping\Annotation\Slug;
use Gedmo\Translatable\Translatable;
use Symfony\Component\PropertyAccess\PropertyAccess;

#[Gedmo\TranslationEntity(class: PageTranslation::class)]
class PageTranslatable implements PageInterface, Translatable
{
    const TYPE_DEFAULT = 'default_translatable';

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

    /**
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    #[Gedmo\Locale]
    private ?string $locale = null;
    private ?iterable $translations = null;

    use PublishableTrait;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
        $this->translations = new ArrayCollection();
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
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function setExcerpt(string $excerpt): self
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
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

    public function removeTranslation(PageTranslation $t)
    {
        $this->translations->removeElement($t);
        $t->setObject(null);
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    public function addTranslation(PageTranslation $t)
    {
        if (!$this->translations->contains($t)) {
            $this->translations[] = $t;
            $t->setObject($this);
        }
    }

    // method used when values is set throught a type collection (add new throught the data-prototype)
    public function setTranslations($at)
    {
        foreach ($at as $t) {
            $this->addTranslation($t);
        }
        return $this;
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getLocales()
    {
        $languages = [];

        foreach ($this->getTranslations() as $translation) {
            if (!in_array($translation->getLocale(), $languages)) {
                $languages[] = $translation->getLocale();
            }
        }

        return implode(", ", $languages);
    }

}
