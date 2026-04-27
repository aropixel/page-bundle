<?php

namespace Aropixel\PageBundle\Tests\Unit\Component\Builder;

use Aropixel\PageBundle\Component\Builder\BootstrapPageBuilderRenderer;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class TestableBootstrapPageBuilderRenderer extends BootstrapPageBuilderRenderer
{
    public function mapJustifyContentPublic(?string $justify): string
    {
        return $this->mapJustifyContent($justify);
    }

    public function mapVerticalAlignPublic(?string $align): string
    {
        return $this->mapVerticalAlign($align);
    }

    public function mapHorizontalAlignPublic(?string $align): string
    {
        return $this->mapHorizontalAlign($align);
    }

    public function mapWidthToBootstrapPublic(array $widths): string
    {
        return $this->mapWidthToBootstrap($widths);
    }

    public function isExternalUrlPublic(string $url): bool
    {
        return $this->isExternalUrl($url);
    }
}

class BootstrapPageBuilderRendererTest extends TestCase
{
    private TestableBootstrapPageBuilderRenderer $renderer;

    protected function setUp(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $requestStack = $this->createMock(RequestStack::class);
        $twig = $this->createMock(Environment::class);
        $cacheManager = $this->createMock(CacheManager::class);

        $this->renderer = new TestableBootstrapPageBuilderRenderer(
            $urlGenerator,
            $requestStack,
            $twig,
            $cacheManager,
        );
    }

    // --- mapJustifyContent ---

    public function testMapJustifyContentCenter(): void
    {
        $this->assertSame('justify-content-center', $this->renderer->mapJustifyContentPublic('center'));
    }

    public function testMapJustifyContentSpaceBetween(): void
    {
        $this->assertSame('justify-content-between', $this->renderer->mapJustifyContentPublic('space-between'));
    }

    public function testMapJustifyContentNullReturnsEmpty(): void
    {
        $this->assertSame('', $this->renderer->mapJustifyContentPublic(null));
    }

    public function testMapJustifyContentUnknownReturnsEmpty(): void
    {
        $this->assertSame('', $this->renderer->mapJustifyContentPublic('unknown'));
    }

    // --- mapVerticalAlign ---

    public function testMapVerticalAlignTop(): void
    {
        $this->assertSame('align-items-start', $this->renderer->mapVerticalAlignPublic('top'));
    }

    public function testMapVerticalAlignCenter(): void
    {
        $this->assertSame('align-items-center', $this->renderer->mapVerticalAlignPublic('center'));
    }

    public function testMapVerticalAlignNullReturnsEmpty(): void
    {
        $this->assertSame('', $this->renderer->mapVerticalAlignPublic(null));
    }

    // --- mapHorizontalAlign ---

    public function testMapHorizontalAlignLeft(): void
    {
        $this->assertSame('text-start', $this->renderer->mapHorizontalAlignPublic('left'));
    }

    public function testMapHorizontalAlignCentreMixedCase(): void
    {
        $this->assertSame('text-center', $this->renderer->mapHorizontalAlignPublic('Centre'));
    }

    public function testMapHorizontalAlignNullReturnsDefault(): void
    {
        $this->assertSame('text-start', $this->renderer->mapHorizontalAlignPublic(null));
    }

    public function testMapHorizontalAlignUnknownReturnsDefault(): void
    {
        $this->assertSame('text-start', $this->renderer->mapHorizontalAlignPublic('unknown'));
    }

    // --- mapWidthToBootstrap ---

    public function testMapWidthSingleBreakpoint(): void
    {
        $this->assertSame('col-6', $this->renderer->mapWidthToBootstrapPublic(['s' => '1-2']));
    }

    public function testMapWidthMultipleBreakpoints(): void
    {
        $this->assertSame('col-12 col-sm-6', $this->renderer->mapWidthToBootstrapPublic(['s' => '1-1', 'm' => '1-2']));
    }

    public function testMapWidthEmptyReturnsDefault(): void
    {
        $this->assertSame('col-12', $this->renderer->mapWidthToBootstrapPublic([]));
    }

    // --- isExternalUrl ---

    public function testIsExternalUrlTrue(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(null);

        $renderer = new TestableBootstrapPageBuilderRenderer(
            $this->createMock(UrlGeneratorInterface::class),
            $requestStack,
            $this->createMock(Environment::class),
            $this->createMock(CacheManager::class),
        );

        $this->assertTrue($renderer->isExternalUrlPublic('https://example.com'));
    }

    // --- render() ---

    public function testRenderNullReturnsEmpty(): void
    {
        $this->assertSame('', $this->renderer->render(null));
    }

    public function testRenderEmptyStringReturnsEmpty(): void
    {
        $this->assertSame('', $this->renderer->render(''));
    }

    public function testRenderInvalidJsonReturnsEmpty(): void
    {
        $this->assertSame('', $this->renderer->render('not-json'));
    }

    public function testRenderValidJsonStringReturnsHtml(): void
    {
        $payload = $this->buildPayload([['type' => 'title', 'content' => 'Hello', 'size' => 'h2']]);
        $result = $this->renderer->render(json_encode($payload));
        $this->assertNotEmpty($result);
    }

    public function testRenderArrayPayloadSameAsJsonString(): void
    {
        $payload = $this->buildPayload([['type' => 'title', 'content' => 'Hello', 'size' => 'h2']]);
        $fromArray = $this->renderer->render($payload);
        $fromJson = $this->renderer->render(json_encode($payload));
        $this->assertSame($fromJson, $fromArray);
    }

    public function testRenderTitleBlockContainsTagAndContent(): void
    {
        $payload = $this->buildPayload([['type' => 'title', 'content' => 'Hello', 'size' => 'h2']]);
        $result = $this->renderer->render($payload);
        $this->assertStringContainsString('<h2', $result);
        $this->assertStringContainsString('Hello', $result);
    }

    public function testRenderTextBlockContainsContent(): void
    {
        $payload = $this->buildPayload([['type' => 'text', 'content' => '<p>Mon texte</p>']]);
        $result = $this->renderer->render($payload);
        $this->assertStringContainsString('Mon texte', $result);
    }

    public function testRenderSpacerBlockContainsDiv(): void
    {
        $payload = $this->buildPayload([['type' => 'spacer', 'height' => 40]]);
        $result = $this->renderer->render($payload);
        $this->assertStringContainsString('<div', $result);
    }

    public function testRenderDividerBlockContainsHr(): void
    {
        $payload = $this->buildPayload([['type' => 'divider']]);
        $result = $this->renderer->render($payload);
        $this->assertStringContainsString('<hr', $result);
    }

    public function testRenderUnknownBlockDoesNotThrow(): void
    {
        $payload = $this->buildPayload([['type' => 'unknown']]);
        $result = $this->renderer->render($payload);
        $this->assertIsString($result);
    }

    /**
     * Builds a minimal valid payload with the given blocks in a single section/row/column.
     */
    private function buildPayload(array $blocks): array
    {
        return [
            'sections' => [
                [
                    'background' => null,
                    'layout' => 'container',
                    'visibleDesktop' => true,
                    'visibleMobile' => true,
                    'rows' => [
                        [
                            'slider' => false,
                            'type' => 'default',
                            'align' => null,
                            'justify' => null,
                            'columns' => [
                                [
                                    'widths' => ['s' => '1-1'],
                                    'align' => null,
                                    'link' => null,
                                    'blocks' => $blocks,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
