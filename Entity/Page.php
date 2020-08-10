<?php

namespace Aropixel\PageBundle\Entity;

use Aropixel\AdminBundle\Entity\Publishable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\PropertyAccess\PropertyAccess;


/**
 * Page
 */
class Page implements PageInterface
{
    const TYPE_DEFAULT = 'default';

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
    protected $type;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $slug;

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
     * @var FieldInterface[]|ArrayCollection
     */
    protected $fields;

    /**
     * @var array
     */
    protected $fieldValues;



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
            $key = $field->getRootKey();
            $value = $field->getExplodedValue();
//            dump($key, $value);

            //
//            if (!array_key_exists($key, $this->fieldValues)) {
//                $this->fieldValues[$key] = $value;
//            }
//            else {
                $this->fieldValues = array_replace_recursive($this->fieldValues, $value);
//            }
        }

    }


    public function getField($key)
    {
        //
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        //
        try {
            return $propertyAccessor->getValue($this, $key);
        }
        catch (\Exception $e) {

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
