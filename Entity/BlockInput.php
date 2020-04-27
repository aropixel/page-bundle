<?php

namespace Aropixel\PageBundle\Entity;

class BlockInput
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
    private $type;

    /**
     * @var string
     */
    /*private $help;*/

    /**
     * @var string
     */
    private $content;


    /**
     * @var Block
     */
    private $block;

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
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent( $content ): void
    {
        $this->content = $content;
    }

    /**
     * @return Block
     */
    public function getBlock(): Block
    {
        return $this->block;
    }

    /**
     * @param Block $block
     */
    public function setBlock( Block $block ): void
    {
        $this->block = $block;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType( string $type ): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    /*public function getHelp(): string
    {
        return $this->help;
    }*/

    /**
     * @param string $help
     */
    /*public function setHelp( string $help ): void
    {
        $this->help = $help;
    }*/

}
