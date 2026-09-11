<?php

namespace Aropixel\PageBundle\Tests\Unit\Component\Builder;

use Aropixel\PageBundle\Component\Builder\CustomBlockRendererInterface;
use Aropixel\PageBundle\Component\Builder\PageUrlGeneratorInterface;
use Aropixel\PageBundle\Component\Builder\UiKitPageBuilderRenderer;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

/**
 * Declaring a custom block and writing its JavaScript makes it editable, not renderable: without a
 * renderer the block is stored in the page and dropped from the output.
 */
class CustomBlockRenderingTest extends TestCase
{
    public function testACustomBlockIsRenderedByItsRenderer(): void
    {
        $html = $this->render([$this->rendererFor('my-widget', '<aside>widget</aside>')]);

        self::assertStringContainsString('<aside>widget</aside>', $html);
    }

    public function testAnUnclaimedTypeRendersNothingRatherThanFailing(): void
    {
        $html = $this->render([$this->rendererFor('something-else', '<aside>nope</aside>')]);

        self::assertStringNotContainsString('nope', $html);
    }

    public function testTheFirstRendererClaimingTheTypeWins(): void
    {
        $html = $this->render([
            $this->rendererFor('my-widget', '<aside>premier</aside>'),
            $this->rendererFor('my-widget', '<aside>second</aside>'),
        ]);

        self::assertStringContainsString('premier', $html);
        self::assertStringNotContainsString('second', $html);
    }

    /**
     * @param list<CustomBlockRendererInterface> $renderers
     */
    private function render(array $renderers): string
    {
        $renderer = new UiKitPageBuilderRenderer(
            $this->createMock(PageUrlGeneratorInterface::class),
            $this->createMock(RequestStack::class),
            $this->createMock(Environment::class),
            $this->createMock(CacheManager::class),
            $renderers,
        );

        return $renderer->render(['sections' => [[
            'rows' => [['columns' => [['blocks' => [['type' => 'my-widget']]]]]],
        ]]]);
    }

    private function rendererFor(string $type, string $html): CustomBlockRendererInterface
    {
        return new class($type, $html) implements CustomBlockRendererInterface {
            public function __construct(private readonly string $type, private readonly string $html)
            {
            }

            public function supports(string $type): bool
            {
                return $type === $this->type;
            }

            public function render(array $block): string
            {
                return $this->html;
            }
        };
    }
}
