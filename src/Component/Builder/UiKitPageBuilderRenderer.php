<?php

namespace Aropixel\PageBundle\Component\Builder;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Render the JSON produced by the admin Page Builder into UIKit-based HTML.
 */
class UiKitPageBuilderRenderer implements PageBuilderRendererInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack,
        private readonly Environment $twig,
        private readonly CacheManager $cacheManager,
    ) {
    }
    /**
     * @param array|string|null $content The page-builder payload. Either the full payload {sections: [...]} or directly an array of sections.
     */
    public function render(array|string|null $content): string
    {
        if ($content === null || $content === '') {
            return '';
        }

        // Accept JSON string
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $content = $decoded;
            } else {
                return '';
            }
        }

        // Accept either {sections: [...]} or just [...]
        $sections = $content['sections'] ?? (is_array($content) ? $content : []);
        if (!is_array($sections) || count($sections) === 0) {
            return '';
        }

        $html = '';

        foreach ($sections as $section) {
            // Visibility (default true if missing)
            $visibleDesktop = $section['visibleDesktop'] ?? true;
            $visibleMobile = $section['visibleMobile'] ?? true;

            $sectionClasses = ['uk-section'];
            $sectionStyle = '';

            $background = $section['background'];
            if ($background) {
                $backgroundType = $background['type'] ?? null;

                if ($backgroundType === 'class') {
                    $sectionClasses[] = $background['value'];
                } else if ($backgroundType === 'image') {
                    $src = $this->resolveImagePath($background['value']);
                    $sectionStyle .= 'background-image:url(' . htmlspecialchars($src) . ');background-size:cover;background-position:center;background-repeat: no-repeat;';
                } else if ($backgroundType === 'color') {
                    $sectionStyle .= 'background-color:' . htmlspecialchars($background['value']) . ';';
                }
            }

            // Layout mapping: "container" (default) -> uk-container; "full" -> none
            $layout = $section['layout'] ?? 'container';

            $visibilityClassBefore = '';
            $visibilityClassAfter = '';

            if (!$visibleDesktop && $visibleMobile) {
                // Hide on desktop and up
                $visibilityClassBefore = '<div class="uk-hidden@m">';
                $visibilityClassAfter = '</div>';
            } elseif ($visibleDesktop && !$visibleMobile) {
                // Show on desktop only
                $visibilityClassBefore = '<div class="uk-visible@m">';
                $visibilityClassAfter = '</div>';
            } elseif (!$visibleDesktop && !$visibleMobile) {
                // Hidden everywhere
                continue;
            }

            $inner = '';

            // Rows
            $rows = $section['rows'] ?? [];
            foreach ($rows as $row) {

                $slider = $row['slider'];

                $rowAlign = $row['align'] ?? null;
                $gridClasses = ['uk-grid'];
                if ($rowAlign) {
                    $gridClasses[] = $this->mapVerticalAlign($rowAlign);
                }

                $rowJustifyContent = $row['justify'] ?? null;
                if ($rowAlign) {
                    $gridClasses[] = $this->mapJustifyContent($rowJustifyContent);
                }

                if ($row['type'] === 'collapse') {
                    $gridClasses[] = 'uk-grid-collapse';
                    $sectionClasses[] = 'uk-padding-remove';
                }

                $colsHtml = '';

                $columns = $row['columns'] ?? [];
                foreach ($columns as $key => $col) {
                    $widths = [
                        'xl' => $col['width']['xl'] ?? null,
                        'l' => $col['width']['l'] ?? null,
                        'm' => $col['width']['m'] ?? null,
                        's' => $col['width']['s'] ?? null,
                    ];

                    $colClasses = [$this->mapWidthToUIKit($widths)];

                    if ($row['reverseMobile'] && 0 == $key) {
                        $colClasses[] = 'uk-flex-last uk-flex-first@m';
                    }

                    // Horizontal alignment (left | center | right)
                    $hAlign = $col['horizontalAlignment'] ?? 'center';
                    $alignClass = $this->mapHorizontalAlign($hAlign);
                    if ($alignClass) {
                        $colClasses[] = $alignClass;
                    }

                    // Background handling
                    $style = 'height: 100%; box-sizing: border-box;';
                    if (!empty($col['background']) && is_array($col['background'])) {
                        $bg = $col['background'];
                        if (!empty($bg['value'])) {
                            $style .= 'padding:20px;';
                        }
                        if (($bg['type'] ?? null) === 'color' && !empty($bg['value'])) {
                            $style .= 'background-color:' . htmlspecialchars($bg['value']) . ';';
                        } elseif (($bg['type'] ?? null) === 'image' && !empty($bg['value'])) {
                            $src = $this->resolveImagePath($bg['value']);
                            $style .= 'background-image:url(' . htmlspecialchars($src) . ');background-size:cover;background-position:center;';
                        } elseif (($bg['type'] ?? null) === 'class' && !empty($bg['value'])) {
                            $colClasses[] = htmlspecialchars($bg['value']);
                        }
                    }

                    /** Si une hauteur est définie, on fait en sorte que le contenu soit centré verticalement */
                    if ($col['height'] && 'auto' !== $col['height']) {
                        $colClasses[] = 'uk-height-' . $col['height'];
                        $colClasses[] = 'uk-flex uk-flex-column uk-flex-center';
                    }

                    if (isset($col['borderRadius']) && $col['borderRadius'] > 0) {
                        $style .= 'border-radius:' . (int)$col['borderRadius'] . 'px;overflow:hidden;';
                    }

                    $colUrl = $col['url'] ?? null;
                    if (isset($col['linkType']) && $col['linkType'] === 'page') {
                        $pageUrl = $this->getUrlFromPage($col);
                        if ($pageUrl) {
                            $colUrl = $pageUrl;
                        }
                    }

                    if ($colUrl) {
                        $targetAttribute = '';
                        if ($this->isExternalUrl($colUrl)) {
                            $targetAttribute = 'target="_blank" rel="noopener noreferrer"';
                        }
                        $blocksHtml = '<a href="' . htmlspecialchars($colUrl) . '" ' . $targetAttribute . ' style="display:block;height:100%;width:100%;text-decoration:none;color:inherit;">';
                    } else {
                        $blocksHtml = '';
                    }

                    // Background overlay for image background
                    if (!empty($col['background']) && is_array($col['background']) && ($col['background']['type'] ?? null) === 'image') {
                        $overlayOpacity = $col['background']['overlayOpacity'] ?? 0;
                        if ($overlayOpacity > 0) {
                            $blocksHtml .= sprintf('<div class="uk-overlay-primary uk-position-cover" style="background-color:rgba(34,34,34,%s)"></div>', $overlayOpacity);
                        }
                    }

                    $blocks = $col['blocks'] ?? [];
                    foreach ($blocks as $block) {
                        $blockContent = $this->renderBlock($block);
                        // If there's an overlay, wrap the block content in a relative div to be above the overlay
                        if (!empty($col['background']) && is_array($col['background']) && ($col['background']['type'] ?? null) === 'image' && ($col['background']['overlayOpacity'] ?? 0) > 0) {
                            $blocksHtml .= sprintf('<div style="position:relative;">%s</div>', $blockContent);
                        } else {
                            $blocksHtml .= $blockContent;
                        }
                        if ('banner' === $block['type']) {
                            $sectionClasses[] = 'uk-section-small';
                            $sectionClasses[] = 'bkg-img-primary';
                            $sectionClasses[] = 'scrolling-text';
                        }
                    }
                    $blocksHtml .= $colUrl ? '</a>' : '';

                    // Ensure relative positioning for overlay
                    if (!empty($col['background']) && is_array($col['background']) && ($col['background']['type'] ?? null) === 'image' && ($col['background']['overlayOpacity'] ?? 0) > 0) {
                        $style .= 'position:relative;';
                    }

                    $colsHtml .= '<div class="' . implode(' ', array_filter(array_unique($colClasses))) . '"><div' . ($style ? ' style="' . $style . '"' : '') . '>' . $blocksHtml . '</div></div>';
                }

                if (null !== $slider) {
                    $slider = '
                        <div class="uk-position-relative uk-visible-toggle">
                            <div class="uk-slider-items ' . implode(' ', $gridClasses) . '" uk-grid>' . $colsHtml . '</div>
                            <a class="uk-position-center-left uk-position-small uk-hidden-hover" href uk-slidenav-previous uk-slider-item="previous"></a>
                            <a class="uk-position-center-right uk-position-small uk-hidden-hover" href uk-slidenav-next uk-slider-item="next"></a>
                        </div>';

                    $inner .= '<div uk-slider>' . $slider . '<ul class="uk-slider-nav uk-dotnav uk-flex-center uk-margin-medium-top"></ul></div>';
                } else {
                    $inner .= '<div class="' . implode(' ', $gridClasses) . '" uk-grid>' . $colsHtml . '</div>';
                }

            }

            // Wrap with container depending on layout
            if ('container-expand' === $layout) {
                $inner = '<div>' . $inner . '</div>';
            } else {
                $layout = $layout !== 'container' ? 'uk-' . $layout : '';
                $inner = '<div class="uk-container ' . $layout . '">' . $inner . '</div>';
            }

            $sectionHtml = '<section class="' . implode(' ', $sectionClasses) . '"'
                . ($sectionStyle ? ' style="' . $sectionStyle . '"' : '')
                . '>' . $inner . '</section>';

            $html .= $visibilityClassBefore . $sectionHtml . $visibilityClassAfter;
        }

        return $html;
    }

    private function mapJustifyContent(?string $justify): string
    {
        return match ($justify) {
            'center' => 'uk-flex-center',
            'space-between' => 'uk-flex-between',
            'space-around' => 'uk-flex-around',
            'flex-start' => 'uk-flex-left',
            'flex-end' => 'uk-flex-right',
            default => ''
        };
    }

    private function mapVerticalAlign(?string $align): string
    {
        return match ($align) {
            'top' => 'uk-flex-top',
            'center' => 'uk-flex-middle',
            'bottom' => 'uk-flex-bottom',
            'stretch' => 'uk-flex-stretch',
            default => ''
        };
    }

    private function mapHorizontalAlign(?string $align): string
    {
        $align = is_string($align) ? strtolower($align) : '';
        return match ($align) {
            'left' => 'uk-text-left',
            'center', 'centre' => 'uk-text-center',
            'right' => 'uk-text-right',
            default => 'uk-text-left'
        };
    }

    private function mapWidthToUIKit(array $widths): string
    {
        $breakpoints = [
            's' => '',
            'm' => '@s',
            'l' => '@m',
            'xl' => '@l',
        ];

        $classes = [];
        $prevClass = null;

        foreach ($breakpoints as $key => $suffix) {
            $width = $widths[$key] ?? null;

            if (null === $width ) {
                continue;
            }

            $uikitWidth = 'uk-width-' . $width;

            if ($uikitWidth !== $prevClass) {
                $classes[] = $uikitWidth . $suffix;
                $prevClass = $uikitWidth;
            }
        }

        return count($classes) > 0 ? implode(' ', $classes) : 'uk-width-1-1';
    }

    private function escapeText(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function renderBlock(array $block): string
    {
        $type = $block['type'] ?? '';
        return match ($type) {
            'title' => $this->renderTitleBlock($block),
            'text' => $this->renderTextBlock($block),
            'button' => $this->renderButtonBlock($block),
            'spacer' => $this->renderSpacerBlock($block),
            'divider' => $this->renderDividerBlock($block),
            'image' => $this->renderImageBlock($block),
            'slider' => $this->renderSliderBlock($block),
            'blog' => $this->renderBlogBlock(),
            'nested-row' => $this->renderGrid($block),
            'banner' => $this->renderBanner($block),
            'iframe' => $this->renderIframeBlock($block),
            default => ''
        };
    }

    private function renderTitleBlock(array $block): string
    {
        $text = $this->escapeText((string)($block['content'] ?? ''));
        $size = (string)($block['size'] ?? 'h2');

        // Parser la valeur size pour extraire la balise et le reste
        $parts = explode('-', $size, 2);
        $tag = $parts[0]; // h2, h3, h4, etc.
        $remaining = $parts[1] ?? ''; // heading_underline, heading_36, etc.

        // Valider la balise (sécurité)
//        if (!in_array($tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true)) {
//            $tag = 'h2';
//        }

        $classNames = $remaining !== '' ? explode('_', $remaining) : [];
        $fontSize = null;

        // Si le dernier élément est un nombre, on le traite comme fontSize
        if (count($classNames) > 0) {
            $lastPart = end($classNames);
            if (is_numeric($lastPart)) {
                $fontSize = $lastPart . 'px';
                array_pop($classNames); // Retirer de la liste des classes
            }
        }

        // Construire l'attribut class
        $classAttr = '';
        if (count($classNames) > 0) {
            $classAttr = ' class="' . htmlspecialchars(implode(' ', $classNames), ENT_QUOTES, 'UTF-8') . '"';
        }

        // Construire l'attribut style
        $styleAttr = '';
        if ($fontSize) {
            $styleAttr = ' style="font-size: ' . htmlspecialchars($fontSize, ENT_QUOTES, 'UTF-8') . ';"';
        }

        $alignment = '';
        if ($block['horizontalAlignment']) {
            $alignment = ' class="uk-text-' . $block['horizontalAlignment'] . '"';
        }

        return sprintf('<div%1$s><%2$s%3$s%5$s>%4$s</%2$s></div>', $alignment, $tag, $classAttr, $text, $styleAttr);
    }

    private function renderTextBlock(array $block): string
    {
        // content may contain basic HTML; trust it but strip <script>
        $html = (string)($block['content'] ?? '');
        $html = preg_replace('#<script\b[^>]*>(.*?)</script>#is', '', $html) ?? '';

        // Remplacer les classes Quill par les classes UIKit
        $replacements = [
            'ql-align-center' => 'uk-text-center',
            'ql-align-right' => 'uk-text-right',
            'ql-align-left' => 'uk-text-left',
            'ql-align-justify' => 'uk-text-justify',
        ];
        $html = str_replace(array_keys($replacements), array_values($replacements), $html);

        // Retirer target="_blank" aux liens internes
        $html = preg_replace_callback('/<a\s+[^>]*href="([^"]+)"[^>]*>/i', function ($matches) {
            $url = $matches[1];
            $link = $matches[0];
            if (!$this->isExternalUrl($url)) {
                return str_replace('target="_blank"', '', $link);
            }
            return $link;
        }, $html);

        return $html;
    }

    public function getUrlFromPage(array $data): ?string
    {
        $pagePath = $data['pagePath'] ?? null;
        $parentSlug = $data['parentSlug'] ?? null;

        // Si on a les deux slugs, on peut générer l'URL sans requête DB
        if ($pagePath) {
            $fullPath = $parentSlug ? $parentSlug . '/' . $pagePath : $pagePath;

            return $this->urlGenerator->generate('front_page_show', ['fullPath' => $fullPath], UrlGeneratorInterface::RELATIVE_PATH);
        }

        return null;
    }

    private function renderButtonBlock(array $block): string
    {
        $label = $this->escapeText((string)($block['label'] ?? ''));
        $url = (string)($block['url'] ?? '#');
        $linkType = $block['linkType'] ?? null;

        if ($linkType === 'page') {
            $pageUrl = $this->getUrlFromPage($block);
            if ($pageUrl) {
                $url = $pageUrl;
            }
        }

        $url = $this->escapeText($url);
        $class = $block['horizontalAlignment'] ? 'uk-text-' . $block[ 'horizontalAlignment'] : '';
        $colorClass = $block['class'] ?? null;

        // pour l'instant, on récupère le style directement dans le css selon le fond
//        $variant = (string)($block['variant'] ?? 'primary');
//        $variantClass = match ($variant) {
//            'secondary' => 'uk-button-secondary',
//            'danger' => 'uk-button-danger',
//            'text' => 'uk-button-text',
//            default => 'uk-button-primary',
//        };
        $targetAttribute = '';
        if ($this->isExternalUrl($url)) {
            $targetAttribute = ' target="_blank" rel="noopener noreferrer"';
        }
        return sprintf('<p class="%s"><a href="%s" class="uk-button uk-button-default %s"%s>%s</a></p>', $class, $url, $colorClass, $targetAttribute, $label);
    }

    private function renderIframeBlock(array $block): string
    {
        $content = (string)($block['content'] ?? '');
        $renderingMode = $block['renderingMode'] ?? 'normal';

        // On autorise l'iframe mais on peut vouloir nettoyer un peu si besoin
        // Ici on part du principe que l'utilisateur sait ce qu'il fait (admin)
        if ($renderingMode === 'ratio169') {
            return sprintf('<div class="video-responsive">%s</div>', $content);
        }

        return sprintf('<div class="uk-responsive-width">%s</div>', $content);
    }

    private function renderBanner(array $block): string
    {
        $text = $block['content'] ?? '';

        return sprintf('<div class="scrolling-content">%s</div>', $text);
    }

    private function isExternalUrl(string $url): bool
    {
        // Récupérer la requête actuelle pour obtenir le domaine
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            // Si pas de requête (contexte CLI par exemple), considérer comme externe par sécurité
            return true;
        }

        // Extraire le host de l'URL à tester
        $urlHost = parse_url($url, PHP_URL_HOST);
        if (!$urlHost) {
            return false;
        }

        $currentHost = $request->getHost();

        return strcasecmp($urlHost, $currentHost) !== 0;
    }

    private function renderSpacerBlock(array $block): string
    {
        $h = (int)($block['height'] ?? 20);
        return '<div style="height:' . $h . 'px"></div>';
    }

    private function renderDividerBlock(array $block): string
    {
        $mt = (int)($block['marginTop'] ?? 20);
        $mb = (int)($block['marginBottom'] ?? 20);
        return '<hr class="" style="margin-top:' . $mt . 'px;margin-bottom:' . $mb . 'px" />';
    }

    private function resolveImagePath(string $src): string
    {
        $path = explode('/images/', $src);
        return $this->cacheManager->getBrowserPath('/images/' . $path[1], 'page');
    }

    private function renderImageBlock(array $block): string
    {
        $src = (string)($block['src'] ?? '');
        if ($src === '') {
            return '';
        }
        $src = $this->resolveImagePath($src);
        $alt = $this->escapeText((string)($block['alt'] ?? ''));
        $useOriginal = (bool)($block['useOriginalSize'] ?? false);
        $width = (int)($block['width'] ?? 100);
        $style = '';
        if (!$useOriginal) {
            $style = ' style="width:' . max(1, min(100, $width)) . '%;height:auto"';
        }

        $alignment = '';
        if (isset($block['horizontalAlignment']) && $block['horizontalAlignment']) {
            $alignment = ' uk-text-' . $block['horizontalAlignment'];
        }

        return sprintf('<div class="uk-inline%s"><img src="%s" alt="%s"%s/></div>', $alignment, $this->escapeText($src), $alt, $style);
    }

    private function renderSliderBlock(array $block): string
    {
        if (empty($block['items'])) {
            return '';
        }

        $items = [];
        foreach ($block['items'] as $img) {
            $src = (string)($img['src'] ?? '');
            if ($src === '') {
                return '';
            }
            $src = $this->resolveImagePath($src);
            $alt = $this->escapeText((string)($img['alt'] ?? ''));
            $items[] = ['src' => $src, 'alt' => $alt];
        }

        try {
            $html = $this->twig->render('@AropixelPage/builder/modules/_slider.html.twig', [
                'items' => $items
            ]);
            return $html ?: '';
        } catch (\Throwable $e) {
            // Fallback silencieux en cas d'erreur
            return '';
        }
    }

    private function renderBlogBlock(): string
    {
        // Rendu serveur via sous‑requête vers la preview admin
        try {
            return $this->blogRenderer->renderTwig();
        } catch (\Throwable $e) {
            // Fallback silencieux en cas d'erreur
        }

        return '';
    }

    private function renderGrid(array $block): string
    {
        $row = $block['row'];
        if (empty($row)) {
            return '';
        }

        $html = '<div class="uk-grid-match uk-child-width-auto uk-text-center uk-flex-center uk-scrollspy-inview" uk-grid>';
        foreach ($row['columns'] as $col) {

            $style = '';
            $colClasses = [];
            if (!empty($col['background']) && is_array($col['background'])) {
                $bg = $col['background'];
                if (($bg['type'] ?? null) === 'color' && !empty($bg['value'])) {
                    $style .= 'background-color:' . htmlspecialchars($bg['value']) . ';';
                } elseif (($bg['type'] ?? null) === 'image' && !empty($bg['value'])) {
                    $src = $this->resolveImagePath($bg['value']);
                    $style .= 'background-image:url(' . htmlspecialchars($src) . ');background-size:cover;background-position:center;';
                } elseif (($bg['type'] ?? null) === 'class' && !empty($bg['value'])) {
                    $colClasses[] = htmlspecialchars($bg['value']);
                }
            }

            $blocksHtml = '<div>';
            $blocks = $col['blocks'] ?? [];
            foreach ($blocks as $block) {
                $blocksHtml .= $this->renderBlock($block);
            }

            $colHtml = sprintf(
                '<div><div class="%s"%s>%s</div></div>',
                implode(' ', array_filter(array_unique($colClasses))),
                $style ? ' style="' . $style . '"' : '',
                $blocksHtml
            );

            $html .= $colHtml;
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

}
