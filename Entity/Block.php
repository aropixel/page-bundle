<?php

namespace Aropixel\PageBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Block
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
     * @var Page
     */
    private $page;

    /**
     * @var string
     */
    /*private $backUpConfig;*/

    /**
     * @var BlockInput[]
     */
    protected $inputs;

    public function __construct()
    {
        $this->inputs = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
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
     * @param string $code
     */
    public function setCode( string $code ): void
    {
        $this->code = $code;
    }

    /**
     * @return Page
     */
    public function getPage(): Page
    {
        return $this->page;
    }

    /**
     * @param Page $page
     */
    public function setPage( Page $page ): void
    {
        $this->page = $page;
    }


    public function addInput(BlockInput $input): self
    {
        if (!$this->inputs->contains($input)) {
            $this->inputs[] = $input;
            $input->setBlock($this);
        }

        return $this;
    }


    public function removeInput(BlockInput $input): self
    {
        if ($this->inputs->contains($input)) {
            $this->inputs->removeElement($input);
            // set the owning side to null (unless already changed)
            if ($input->getBlock() === $this) {
                $input->setBlock(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BlockInput[]
     */
    public function getInputs(): Collection
    {
        return $this->inputs;
    }

    /**
     * @return string
     */
    /*public function getBackUpConfig(): string
    {
        return $this->backUpConfig;
    }*/

    /**
     * @param string $backUpConfig
     */
    /*public function setBackUpConfig( string $backUpConfig ): void
    {
        $this->backUpConfig = $backUpConfig;
    }*/


}
