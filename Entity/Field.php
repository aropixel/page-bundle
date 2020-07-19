<?php

namespace Aropixel\PageBundle\Entity;


class Field implements FieldInterface
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $formType;

    /**
     * @var string
     */
    private $value;

    /**
     * @var ?string
     */
    private $attributes;

    /**
     * @var string
     */
    private $crops;

    /**
     * @var Page
     */
    private $page;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return string
     */
    public function getCrops()
    {
        return $this->crops;
    }

    /**
     * @param string $crops
     * @return Field
     */
    public function setCrops($crops): FieldInterface
    {
        $this->crops = $crops;
        return $this;
    }

    /**
     * @return Page
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


}
