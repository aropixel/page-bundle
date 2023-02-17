<?php

namespace Aropixel\PageBundle\Entity;

use Aropixel\AdminBundle\Entity\CropInterface;
use Aropixel\AdminBundle\Entity\CroppableInterface;
use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Entity\ImageInterface;


class Field implements FieldInterface, ImageInterface, CroppableInterface
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

    private ?Page $page = null;



    public function __toString()
    {
        return !is_null($this->getValue()) ? $this->getValue() : '';
    }


    /**
     * @return ?int
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Field
     */
    public function setId(int $id): Field
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getRootKey(): string
    {
        $codes = explode('.', $this->code);
        return current($codes);
    }

    /**
     * @return string
     */
    public function getExplodedValue($keys=null, $value=null)
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


    /**
     * @return string
     */
    public function getFormType()
    {
        return $this->formType;
    }

    /**
     * @param string $formType
     * @return Field
     */
    public function setFormType(string $formType): Field
    {
        $this->formType = $formType;
        return $this;
    }

    /**
     * @param string $code
     */
    public function setCode( string $code ): void
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return Field
     */
    public function setValue($value): FieldInterface
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param mixed $attributes
     * @return Field
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @return array
     */
    public function getCrops()
    {
        return $this->crops;
    }

    /**
     * @param array $crops
     * @return Field
     */
    public function setCrops($crops): FieldInterface
    {
        $this->crops = $crops;
        return $this;
    }

    /**
     * @return ?Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param Page $page
     * @return Field
     */
    public function setPage(Page $page): FieldInterface
    {
        $this->page = $page;
        return $this;
    }



    public function getFilename()
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

    public function getWebPath()
    {
        return Image::getFileNameWebPath($this->value);
    }


}
