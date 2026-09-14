<?php

namespace Aropixel\PageBundle\Component\Builder;

/**
 * The colours a section may set on its content, from `page_builder.section_colors`.
 *
 * A background is chosen freely, so what sits on it must be able to follow: the site's default ink on
 * a dark section is unreadable. Which colours are offered is an application's decision, not the
 * bundle's — a footer wants text, titles and links, a hero might want a button or an icon colour —
 * so the list is configuration, and the three defaults are only what most pages need.
 *
 * Each colour writes a custom property on the section, because an inline style cannot reach a
 * descendant: the application's stylesheet consumes it once, and decides what the colour applies to.
 *
 *     .my-page a { color: var(--pb-link-color, inherit); }
 *
 * One colour may be `inherited`, which also writes it as `color` on the section so every block picks
 * it up without a rule. That is the text colour, and marking two would simply mean the last one wins.
 *
 * Values are stored in the payload as `<key>Color` — `textColor`, `linkColor` — one key per colour,
 * beside the other section properties rather than nested, so a page saved before this list existed
 * still reads.
 */
class SectionColors
{
    /**
     * Kept here rather than only in the configuration tree so a renderer built by hand — a test, a
     * one-off script — behaves like a configured one.
     */
    public const DEFAULTS = [
        ['key' => 'text', 'label' => 'page.builder.inspector.text_color', 'variable' => '--pb-text-color', 'inherited' => true],
        ['key' => 'title', 'label' => 'page.builder.inspector.title_color', 'variable' => '--pb-title-color', 'inherited' => false],
        ['key' => 'link', 'label' => 'page.builder.inspector.link_color', 'variable' => '--pb-link-color', 'inherited' => false],
    ];

    /**
     * @param list<array{key: string, label?: string, variable: string, inherited?: bool}> $colors
     */
    public function __construct(
        private readonly array $colors = self::DEFAULTS,
    ) {
    }

    /**
     * The `style` fragment a section's colours contribute, ready to be appended to the others.
     *
     * A colour left empty writes nothing at all: the site's own styles apply, which is what makes the
     * inspector's "default" button mean something.
     *
     * @param array<string, mixed> $section
     */
    public function styleFor(array $section): string
    {
        $style = '';

        foreach ($this->colors as $color) {
            $value = $section[$color['key'] . 'Color'] ?? null;

            if (!is_string($value) || $value === '') {
                continue;
            }

            $escaped = htmlspecialchars($value);

            if ($color['inherited'] ?? false) {
                $style .= 'color:' . $escaped . ';';
            }

            $style .= $color['variable'] . ':' . $escaped . ';';
        }

        return $style;
    }
}
