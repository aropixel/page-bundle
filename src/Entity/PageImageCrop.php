<?php

namespace Aropixel\PageBundle\Entity;

use Aropixel\AdminBundle\Entity\AttachedImageInterface;
use Aropixel\AdminBundle\Entity\Crop;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: "aropixel_page_image_crop")]
class PageImageCrop extends Crop
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PageImage::class, inversedBy: "crops")]
    private ?PageImage $image = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): AttachedImageInterface
    {
        return $this->image;
    }

    public function setImage(?PageImage $image): self
    {
        $this->image = $image;

        return $this;
    }
}
