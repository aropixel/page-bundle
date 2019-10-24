<?php

namespace Aropixel\PageBundle\Entity;

use Aropixel\AdminBundle\Entity\AttachImage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


/**
 * PageImage
 */
class PageImage extends AttachImage
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var Page
     */
    private $page;

    /**
     * @var PageImageCrop[]
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

        // set (or unset) the owning side of the relation if necessary
        $newImage = $page === null ? null : $this;
        if ($newImage !== $page->getImage()) {
            $page->setImage($newImage);
        }

        return $this;
    }

    /**
     * @return Collection|PageImageCrop[]
     */
    public function getCrops(): Collection
    {
        return $this->crops;
    }



    public function addCrop(PageImageCrop $crop): self
    {
        if (!$this->crops->contains($crop)) {
            $this->crops[] = $crop;
            $crop->setImage($this);
        }

        return $this;
    }


    public function removeCrop(PageImageCrop $crop): self
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
