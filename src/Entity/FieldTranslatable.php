<?php

namespace Aropixel\PageBundle\Entity;

use Aropixel\AdminBundle\Entity\CroppableInterface;
use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Entity\ImageInterface;
use Gedmo\Translatable\Translatable;
use Symfony\Component\HttpFoundation\File\File;


class FieldTranslatable implements FieldInterface, ImageInterface, CroppableInterface, Translatable
{

    private ?int $id = null;

    /**
     * Field name in the form (can be composed, for collections). Ex : "excerpt", "title", "slideshow.0", "slideshow.1"
     */
    private string $code;

    /**
     * Symfony Form Type of the field : TextType, TextareaType, ImageType
     */
    private string $formType;

    private ?string $value = null;

    private ?array $attributes = null;

    private ?array $crops = null;

    private ?PageTranslatable $page = null;


    public function __toString()
    {
        return !is_null($this->getValue()) ? $this->getValue() : '';
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getRootKey(): string
    {
        $codes = explode('.', $this->code);
        return current($codes);
    }

    public function getExplodedValue($keys = null, $value = null)
    {
        if (is_null($keys)) {
            $keys = explode('.', $this->code);
        }

        if (is_null($value)) {
            $value = $this;
        }

        // Get the first child key
        $currentKey = array_pop($keys);

        // If there's no more child, we're done
        if (is_null($currentKey)) {
            return $value;
        }

        $newValue = [$currentKey => $value];

        return $this->getExplodedValue($keys, $newValue);
    }


    public function getFormType(): string
    {
        return $this->formType;
    }

    public function setFormType(string $formType): self
    {
        $this->formType = $formType;

        return $this;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getAttributes(): mixed
    {
        return $this->attributes;
    }

    public function setAttributes(mixed $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getCrops(): ?array
    {
        return $this->crops;
    }

    public function setCrops(array $crops): self
    {
        $this->crops = $crops;

        return $this;
    }

    public function getPage(): ?PageTranslatable
    {
        return $this->page;
    }

    public function setPage(PageTranslatable $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getFilename() : ?string
    {
        if ($this->formType == 'ImageType' || $this->formType == 'GalleryImageType') {
            return $this->value;
        }

        return null;
    }

    public function getImageUid(): string
    {
        return $this->getId() ?: uniqid();
    }

    public function getCropsInfos(): array
    {
        $cropsInfos = [];
        if (!is_null($this->getCrops())) {

            foreach ($this->getCrops() as $crop) {
                $filterName = $crop['filter'];
                $cropInfo = $crop['crop'];
                $cropsInfos[$filterName] = $cropInfo;
            }

        }

        return $cropsInfos;
    }

    public function getWebPath() : ?string
    {
        return Image::getFileNameWebPath($this->value);
    }

    public function getTempPath(): ?string
    {
        // TODO: Implement getTempPath() method.
    }

    public function getFile(): ?File
    {
        // TODO: Implement getFile() method.
    }


}
