<?php

namespace Aropixel\PageBundle\Entity;

use Aropixel\AdminBundle\Entity\Crop;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Aropixel\PageBundle\Repository\PageGalleryCropRepository")
 */
class PageGalleryCrop extends Crop
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="PageGallery", inversedBy="crops")
     */
    private $image;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): ?PageGallery
    {
        return $this->image;
    }

    public function setImage(?PageGallery $image): self
    {
        $this->image = $image;

        return $this;
    }
}
