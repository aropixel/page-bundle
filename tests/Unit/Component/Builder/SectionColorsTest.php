<?php

namespace Aropixel\PageBundle\Tests\Unit\Component\Builder;

use Aropixel\PageBundle\Component\Builder\SectionColors;
use PHPUnit\Framework\TestCase;

class SectionColorsTest extends TestCase
{
    /**
     * An application that configures nothing gets what most pages need.
     */
    public function testTheDefaultsCoverText_TitlesAndLinks(): void
    {
        $style = (new SectionColors())->styleFor([
            'textColor' => '#ffffff',
            'titleColor' => '#f2b241',
            'linkColor' => '#00d1b2',
        ]);

        self::assertStringContainsString('--pb-text-color:#ffffff;', $style);
        self::assertStringContainsString('--pb-title-color:#f2b241;', $style);
        self::assertStringContainsString('--pb-link-color:#00d1b2;', $style);
    }

    /**
     * The text colour also lands on `color`, which is the whole point of `inherited`: every block
     * takes it without the site writing a rule.
     */
    public function testAnInheritedColourIsAlsoWrittenAsColor(): void
    {
        $style = (new SectionColors())->styleFor(['textColor' => '#ffffff', 'linkColor' => '#00d1b2']);

        self::assertStringContainsString('color:#ffffff;', $style);
        self::assertStringNotContainsString('color:#00d1b2;--pb-link-color', $style, 'Seule la couleur héritée touche `color`.');
    }

    /**
     * A colour left empty writes nothing at all — that is what makes the inspector's "default"
     * button mean something, rather than painting the section black.
     */
    public function testAnEmptyColourWritesNothing(): void
    {
        self::assertSame('', (new SectionColors())->styleFor([]));
        self::assertSame('', (new SectionColors())->styleFor(['textColor' => null, 'linkColor' => '']));
    }

    /**
     * The point of the configuration: a project that wants a fourth colour declares it, instead of
     * asking the bundle for another one.
     */
    public function testAnApplicationChoosesItsOwnList(): void
    {
        $colors = new SectionColors([
            ['key' => 'button', 'label' => 'Boutons', 'variable' => '--my-button-color'],
        ]);

        $style = $colors->styleFor(['buttonColor' => '#ff0000', 'linkColor' => '#00d1b2']);

        self::assertSame('--my-button-color:#ff0000;', $style, 'Une couleur non déclarée n’est pas rendue.');
    }

    /**
     * The value reaches an attribute: a payload written by hand must not be able to close it.
     */
    public function testAValueIsEscaped(): void
    {
        $style = (new SectionColors())->styleFor(['linkColor' => '"><script>']);

        self::assertStringNotContainsString('<script>', $style);
        self::assertStringContainsString('&quot;&gt;', $style);
    }
}
