<?php

namespace Aropixel\PageBundle\Entity;

use Aropixel\AdminBundle\Entity\Crop;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Aropixel\PageBundle\Repository\PageImageCropRepository")
 */
class PageImageCrop extends Crop
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="PageImage", inversedBy="crops")
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
