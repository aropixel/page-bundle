<?php

namespace Aropixel\PageBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Aropixel\PageBundle\Repository\PageGalleryRepository")
 */
class PageGallery
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="gallery")
     */
    private $page;

    /**
     * @ORM\OneToMany(targetEntity="PageImageCrop", mappedBy="image")
     */
    private $crops;



    public function __construct()
    {
        $this->crops = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return Collection|PageGalleryCrop[]
     */
    public function getCrops(): Collection
    {
        return $this->crops;
    }



    public function addCrop(PageGalleryCrop $crop): self
    {
        if (!$this->crops->contains($crop)) {
            $this->crops[] = $crop;
            $crop->setImage($this);
        }

        return $this;
    }


    public function removeCrop(PageGalleryCrop $crop): self
    {
        if ($this->crops->contains($crop)) {
            $this->crops->removeElement($crop);
            // set the owning side to null (unless already changed)
            if ($crop->getImage() === $this) {
                $crop->setImage(null);
            }
        }

        return $this;
    }

}
