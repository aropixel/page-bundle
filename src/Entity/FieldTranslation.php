<?php

namespace Aropixel\PageBundle\Entity;

use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;


class FieldTranslation extends AbstractPersonalTranslation
{
    public function __construct($locale, $field, $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }

    protected $object;
}