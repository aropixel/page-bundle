<?php

declare(strict_types=1);

namespace Aropixel\PageBundle\Twig;

use Aropixel\PageBundle\Block\BlockManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class BlockExtension extends AbstractExtension
{

    /**
     * @var BlockManager
     */
    private $blockManager;

    public function __construct(BlockManager $blockManager)
    {
        $this->blockManager = $blockManager;
    }


    public function getFilters()
    {
        return [
            new TwigFilter('int', [$this, 'castToInt'])
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_input', [$this, 'getTextInput'],['is_safe' => ['html']]),
            new TwigFunction('get_block_name', [$this, 'getBlockName'])
        ];
    }

    public function getTextInput(string $code)
    {
        return $this->blockManager->getDbBlockInput($code)->getContent()['content'];
    }


    public function getBlockName($code)
    {
        return $this->blockManager->getConfiguredBlockNameByCode($code);
    }

    public function castToInt($value)
    {
        $strValue = strval($value);

        return (int) $value;
    }
}
