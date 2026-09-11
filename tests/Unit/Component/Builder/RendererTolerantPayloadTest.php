<?php

namespace Aropixel\PageBundle\Tests\Unit\Component\Builder;

use Aropixel\PageBundle\Component\Builder\BootstrapPageBuilderRenderer;
use Aropixel\PageBundle\Component\Builder\PageBuilderRendererInterface;
use Aropixel\PageBundle\Component\Builder\PageUrlGeneratorInterface;
use Aropixel\PageBundle\Component\Builder\UiKitPageBuilderRenderer;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

/**
 * The renderers read payloads produced by the builder, which always carries every key its JS models
 * define. Two things do not: a payload written by hand — a project shipping starter templates, say —
 * and a page saved before a key was added to those models.
 *
 * Neither may produce a PHP warning: the payload is content, and rendering content must not depend
 * on the version of the editor that happened to write it.
 */
class RendererTolerantPayloadTest extends TestCase
{
    #[DataProvider('provideRenderers')]
    public function testTheBareMinimumPayloadRendersWithoutAWarning(string $class): void
    {
        $this->assertRendersQuietly($this->makeRenderer($class), [
            'sections' => [[
                'rows' => [[
                    'columns' => [[
                        'blocks' => [
                            ['type' => 'title', 'content' => 'Titre'],
                            ['type' => 'text', 'content' => 'Texte'],
                            ['type' => 'divider'],
                            ['type' => 'spacer'],
                        ],
                    ]],
                ]],
            ]],
        ]);
    }

    /**
     * A block nested in a `nested-row` goes through the same code with the same gaps.
     */
    #[DataProvider('provideRenderers')]
    public function testANestedRowPayloadRendersWithoutAWarning(string $class): void
    {
        $this->assertRendersQuietly($this->makeRenderer($class), [
            'sections' => [[
                'rows' => [[
                    'columns' => [[
                        'blocks' => [[
                            'type' => 'nested-row',
                            'row' => ['columns' => [['blocks' => [['type' => 'text', 'content' => 'Imbriqué']]]]],
                        ]],
                    ]],
                ]],
            ]],
        ]);
    }

    /**
     * @return iterable<string, array{class-string<PageBuilderRendererInterface>}>
     */
    public static function provideRenderers(): iterable
    {
        yield 'uikit' => [UiKitPageBuilderRenderer::class];
        yield 'bootstrap' => [BootstrapPageBuilderRenderer::class];
    }

    /**
     * @param array<mixed> $payload
     */
    private function assertRendersQuietly(PageBuilderRendererInterface $renderer, array $payload): void
    {
        $raised = [];
        set_error_handler(static function (int $severity, string $message) use (&$raised): bool {
            $raised[] = $message;

            return true;
        });

        try {
            $html = $renderer->render($payload);
        } finally {
            restore_error_handler();
        }

        self::assertSame([], $raised, "Le rendu a émis :\n" . implode("\n", $raised));
        self::assertNotSame('', $html, 'Un payload valide doit produire du HTML.');
    }

    /**
     * @param class-string<PageBuilderRendererInterface> $class
     */
    private function makeRenderer(string $class): PageBuilderRendererInterface
    {
        return new $class(
            $this->createMock(PageUrlGeneratorInterface::class),
            $this->createMock(RequestStack::class),
            $this->createMock(Environment::class),
            $this->createMock(CacheManager::class),
        );
    }
}
