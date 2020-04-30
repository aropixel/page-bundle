<?php

declare(strict_types=1);

namespace Aropixel\PageBundle\Twig;

use Aropixel\PageBundle\Block\BlockManager;
use Twig\Extension\AbstractExtension;
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

    public function getFunctions()
    {
        return [
            new TwigFunction('get_input', [$this, 'getTextInput'],['is_safe' => ['html']])
        ];
    }

    public function getTextInput(string $code)
    {
        return $this->blockManager->getDbBlockInput($code)->getContent()['content'];
    }
}
