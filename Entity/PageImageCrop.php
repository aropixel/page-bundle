<?php

namespace Aropixel\PageBundle\Entity;

use Aropixel\AdminBundle\Entity\Crop;


/**
 * PageImageCrop
 */
class PageImageCrop extends Crop
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var PageImage
     */
    private $image;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): ?PageImage
    {
        return $this->image;
    }

    public function setImage(?PageImage $image): self
    {
        $this->image = $image;

        return $this;
    }
}
