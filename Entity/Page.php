<?php

namespace Aropixel\PageBundle\Entity;

use Aropixel\AdminBundle\Entity\Publishable;
use Aropixel\AdminBundle\Entity\PublishableTrait;
use Aropixel\PageBundle\Repository\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;



#[ORM\MappedSuperclass]
#[ORM\Table(name: "aropixel_page")]
#[ORM\Entity(repositoryClass: PageRepository::class)]
class Page implements PageInterface
{
    public const TYPE_DEFAULT = 'default';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    protected ?int $id = null;

    #[ORM\Column(name: "status", type: "string", length: 20)]
    protected string $status = Publishable::STATUS_OFFLINE;

    #[ORM\Column(name: "type", type: "string", length: 100)]
    protected string $type;

    #[ORM\Column(name: "code", type: "string", length: 100, nullable: true)]
    protected ?string $code = null;

    #[ORM\Column(name: "title", type: "string", nullable: true)]
    protected ?string $title = null;

    #[ORM\Column(name: "excerpt", type: "text", nullable: true)]
    protected ?string $excerpt = null;

    #[ORM\Column(name: "description", type: "text", nullable: true)]
    protected ?string $description = null;

    #[ORM\Column(name: "slug", type: "string", nullable: true)]
    #[Gedmo\Slug(fields:['title'])]
    protected string $slug;

    #[ORM\Column(name: "meta_title", type: "string", nullable: true)]
    protected ?string $metaTitle = null;

    #[ORM\Column(name: "meta_description", type: "text", nullable: true)]
    protected ?string $metaDescription = null;

    #[ORM\Column(name: "meta_keywords", type: "text", nullable: true)]
    protected ?string $metaKeywords = null;

    #[ORM\Column(name: "created_at", type: "datetime")]
    #[Gedmo\Timestampable(on:"create")]
    protected \DateTimeInterface $createdAt;

    #[ORM\Column(name: "updated_at", type: "datetime", nullable: true)]
    #[Gedmo\Timestampable(on:"update")]
    protected ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(name: "publish_at", type: "datetime", nullable: true)]
    protected ?\DateTimeInterface $publishAt = null;

    #[ORM\Column(name: "publish_until", type: "datetime", nullable: true)]
    protected ?\DateTimeInterface $publishUntil = null;

    #[ORM\OneToMany(targetEntity: FieldInterface::class, mappedBy: "page", cascade: ["persist", "remove"])]
    protected Collection $fields;

    use PublishableTrait;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }


    private function compileFieldsValues()
    {
        //
        $this->fieldValues = [];
        foreach ($this->fields as $field) {

            //
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
        //
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        //
        try {
            return $propertyAccessor->getValue($this, $key);
        }
        catch (\Exception) {

            //
            if (is_null($this->fieldValues)) {
                $this->compileFieldsValues();
            }

            //
            return (array_key_exists($key, $this->fieldValues) ? $this->fieldValues[$key] : null);

        }

    }



    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Page
     */
    public function setType(string $type): PageInterface
    {
        $this->type = $type;return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Page
     */
    public function setCode($code): PageInterface
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Page
     */
    public function setTitle($title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return $this
     */
    public function setSlug($slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return string
     */
    public function getExcerpt()
    {
        return $this->excerpt;
    }

    /**
     * @param string $excerpt
     * @return Page
     */
    public function setExcerpt($excerpt): self
    {
        $this->excerpt = $excerpt;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Page
     */
    public function setDescription($description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    /**
     * @param string|null $metaTitle
     * @return $this
     */
    public function setMetaTitle(?string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    /**
     * @param string|null $metaDescription
     * @return $this
     */
    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    /**
     * @param string|null $metaKeywords
     * @return $this
     */
    public function setMetaKeywords(?string $metaKeywords): self
    {
        $this->metaKeywords = $metaKeywords;
        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface|null $createdAt
     * @return $this
     */
    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeInterface|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getPublishAt(): ?\DateTimeInterface
    {
        return $this->publishAt;
    }

    /**
     * @param \DateTimeInterface|null $publishAt
     * @return $this
     */
    public function setPublishAt(?\DateTimeInterface $publishAt): self
    {
        $this->publishAt = $publishAt;
        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getPublishUntil(): ?\DateTimeInterface
    {
        return $this->publishUntil;
    }

    /**
     * @param \DateTimeInterface|null $publishUntil
     * @return $this
     */
    public function setPublishUntil(?\DateTimeInterface $publishUntil): self
    {
        $this->publishUntil = $publishUntil;
        return $this;
    }

    /**
     * @return Field[]|ArrayCollection
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param Field $field
     */
    public function addField(FieldInterface $field)
    {
        $this->fields->add($field);
        $field->setPage($this);
    }

    /**
     * @param Field $field
     */
    public function removeField(FieldInterface $field)
    {
        $this->fields->removeElement($field);
        $field->setPage(null);
    }


}
