<?php

namespace Aropixel\PageBundle\Entity;

use Aropixel\AdminBundle\Entity\AttachedImage;
use Aropixel\AdminBundle\Entity\CroppableInterface;
use Aropixel\AdminBundle\Entity\CroppableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: "aropixel_page_image")]
class PageImage extends AttachedImage implements CroppableInterface
{

    use CroppableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: PageInterface::class, mappedBy: "image")]
    private ?Page $page = null;

    /**
     * @var Collection<int, PageImageCrop>
     */
    #[ORM\OneToMany(targetEntity: PageImageCrop::class, mappedBy: "image", cascade: ["persist", "remove"])]
    private Collection $crops;


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

    public function setPage(Page|null $page): self
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
     * @return Collection<int, PageImageCrop>
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
