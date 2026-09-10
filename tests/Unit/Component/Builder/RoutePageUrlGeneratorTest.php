<?php

namespace Aropixel\PageBundle\Tests\Unit\Component\Builder;

use Aropixel\PageBundle\Component\Builder\RoutePageUrlGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RoutePageUrlGeneratorTest extends TestCase
{
    public function testItGeneratesTheConfiguredRoute(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with('front_page_show', ['fullPath' => 'contact'], UrlGeneratorInterface::RELATIVE_PATH)
            ->willReturn('contact')
        ;

        $generator = new RoutePageUrlGenerator($urlGenerator, 'front_page_show', 'fullPath');

        self::assertSame('contact', $generator->generate(['pagePath' => 'contact']));
    }

    public function testItPrefixesTheParentSlugWhenHierarchical(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with('front_page_show', ['fullPath' => 'about/team'], UrlGeneratorInterface::RELATIVE_PATH)
            ->willReturn('about/team')
        ;

        $generator = new RoutePageUrlGenerator($urlGenerator, 'front_page_show', 'fullPath');

        self::assertSame('about/team', $generator->generate([
            'pagePath' => 'team',
            'parentSlug' => 'about',
        ]));
    }

    /**
     * The flat convention — an application routing `/page/{slug}` has no room for the parent slug,
     * and prefixing it would produce a slug that matches no page.
     */
    public function testItIgnoresTheParentSlugWhenFlat(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with('app_page', ['slug' => 'team'], UrlGeneratorInterface::RELATIVE_PATH)
            ->willReturn('/page/team')
        ;

        $generator = new RoutePageUrlGenerator($urlGenerator, 'app_page', 'slug', false);

        self::assertSame('/page/team', $generator->generate([
            'pagePath' => 'team',
            'parentSlug' => 'about',
        ]));
    }

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('provideItReturnsNullWhenThePayloadReferencesNoPageCases')]
    public function testItReturnsNullWhenThePayloadReferencesNoPage(array $data): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects(self::never())->method('generate');

        $generator = new RoutePageUrlGenerator($urlGenerator, 'front_page_show', 'fullPath');

        self::assertNull($generator->generate($data));
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function provideItReturnsNullWhenThePayloadReferencesNoPageCases(): iterable
    {
        yield 'missing key' => [[]];
        yield 'null' => [['pagePath' => null]];
        yield 'empty string' => [['pagePath' => '']];
        yield 'not a string' => [['pagePath' => 42]];
    }

    /**
     * A misconfigured route must not stop an author from saving: the renderer falls back to the raw
     * URL, and the warning says where to look.
     */
    public function testItReturnsNullAndWarnsWhenTheRouteDoesNotExist(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->willThrowException(new RouteNotFoundException('front_page_show'))
        ;

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('warning')
            ->with(
                self::stringContains('aropixel_page.page_builder.front_route'),
                self::callback(static fn (array $context): bool => 'front_page_show' === $context['route']
                    && 'contact' === $context['path']),
            )
        ;

        $generator = new RoutePageUrlGenerator($urlGenerator, 'front_page_show', 'fullPath', true, $logger);

        self::assertNull($generator->generate(['pagePath' => 'contact']));
    }

    public function testItSurvivesWithoutALogger(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->willThrowException(new RouteNotFoundException('front_page_show'))
        ;

        $generator = new RoutePageUrlGenerator($urlGenerator, 'front_page_show', 'fullPath');

        self::assertNull($generator->generate(['pagePath' => 'contact']));
    }
}
