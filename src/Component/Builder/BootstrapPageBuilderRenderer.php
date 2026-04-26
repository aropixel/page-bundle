<?php

namespace Aropixel\PageBundle\Component\Builder;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Render the JSON produced by the admin Page Builder into Bootstrap 5-based HTML.
 */
class BootstrapPageBuilderRenderer implements PageBuilderRendererInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack,
        private readonly Environment $twig,
        private readonly CacheManager $cacheManager,
    ) {
    }

    /**
     * @param array|string|null $content The page-builder payload.
     */
    public function render(array|string|null $content): string
    {
        if ($content === null || $content === '') {
            return '';
        }

        if (is_string($content)) {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $content = $decoded;
            } else {
                return '';
            }
        }

        $sections = $content['sections'] ?? (is_array($content) ? $content : []);
        if (!is_array($sections) || count($sections) === 0) {
            return '';
        }

        $html = '';

        foreach ($sections as $section) {
            $visibleDesktop = $section['visibleDesktop'] ?? true;
            $visibleMobile = $section['visibleMobile'] ?? true;

            $sectionClasses = ['py-5'];
            $sectionStyle = '';

            $background = $section['background'];
            if ($background) {
                $backgroundType = $background['type'] ?? null;

                if ($backgroundType === 'class') {
                    $sectionClasses[] = $background['value'];
                } elseif ($backgroundType === 'image') {
                    $src = $this->resolveImagePath($background['value']);
                    $sectionStyle .= 'background-image:url(' . htmlspecialchars($src) . ');background-size:cover;background-position:center;background-repeat:no-repeat;';
                } elseif ($backgroundType === 'color') {
                    $sectionStyle .= 'background-color:' . htmlspecialchars($background['value']) . ';';
                }
            }

            $layout = $section['layout'] ?? 'container';

            $visibilityClassBefore = '';
            $visibilityClassAfter = '';

            if (!$visibleDesktop && $visibleMobile) {
                $visibilityClassBefore = '<div class="d-md-none">';
                $visibilityClassAfter = '</div>';
            } elseif ($visibleDesktop && !$visibleMobile) {
                $visibilityClassBefore = '<div class="d-none d-md-block">';
                $visibilityClassAfter = '</div>';
            } elseif (!$visibleDesktop && !$visibleMobile) {
                continue;
            }

            $inner = '';

            $rows = $section['rows'] ?? [];
            foreach ($rows as $row) {
                $slider = $row['slider'];

                $rowAlign = $row['align'] ?? null;
                $rowJustify = $row['justify'] ?? null;

                $gridClasses = ['row'];

                if ($rowAlign) {
                    $gridClasses[] = $this->mapVerticalAlign($rowAlign);
                }
                if ($rowJustify) {
                    $gridClasses[] = $this->mapJustifyContent($rowJustify);
                }

                if ($row['type'] === 'collapse') {
                    $gridClasses[] = 'g-0';
                    $sectionClasses[] = 'p-0';
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

                    $colClasses = [$this->mapWidthToBootstrap($widths)];

                    if ($row['reverseMobile'] && 0 == $key) {
                        $colClasses[] = 'order-last order-md-first';
                    }

                    $hAlign = $col['horizontalAlignment'] ?? 'center';
                    $alignClass = $this->mapHorizontalAlign($hAlign);
                    if ($alignClass) {
                        $colClasses[] = $alignClass;
                    }

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

                    if ($col['height'] && 'auto' !== $col['height']) {
                        $style .= 'min-height:' . htmlspecialchars((string)$col['height']) . ';';
                        $colClasses[] = 'd-flex flex-column justify-content-center';
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

                    // Overlay for image background
                    if (!empty($col['background']) && is_array($col['background']) && ($col['background']['type'] ?? null) === 'image') {
                        $overlayOpacity = $col['background']['overlayOpacity'] ?? 0;
                        if ($overlayOpacity > 0) {
                            $blocksHtml .= sprintf('<div class="position-absolute top-0 start-0 w-100 h-100" style="background-color:rgba(34,34,34,%s)"></div>', $overlayOpacity);
                        }
                    }

                    $blocks = $col['blocks'] ?? [];
                    foreach ($blocks as $block) {
                        $blockContent = $this->renderBlock($block);
                        if (!empty($col['background']) && is_array($col['background']) && ($col['background']['type'] ?? null) === 'image' && ($col['background']['overlayOpacity'] ?? 0) > 0) {
                            $blocksHtml .= sprintf('<div style="position:relative;">%s</div>', $blockContent);
                        } else {
                            $blocksHtml .= $blockContent;
                        }
                    }
                    $blocksHtml .= $colUrl ? '</a>' : '';

                    if (!empty($col['background']) && is_array($col['background']) && ($col['background']['type'] ?? null) === 'image' && ($col['background']['overlayOpacity'] ?? 0) > 0) {
                        $style .= 'position:relative;';
                    }

                    $colsHtml .= '<div class="' . implode(' ', array_filter(array_unique($colClasses))) . '"><div' . ($style ? ' style="' . $style . '"' : '') . '>' . $blocksHtml . '</div></div>';
                }

                if ($slider) {
                    // Bootstrap Carousel for row-level slider
                    $carouselId = 'carousel-row-' . substr(md5(uniqid('', true)), 0, 8);
                    $inner .= sprintf(
                        '<div id="%s" class="carousel slide" data-bs-ride="carousel"><div class="carousel-inner %s">%s</div>'
                        . '<button class="carousel-control-prev" type="button" data-bs-target="#%s" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>'
                        . '<button class="carousel-control-next" type="button" data-bs-target="#%s" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button></div>',
                        $carouselId,
                        implode(' ', $gridClasses),
                        $colsHtml,
                        $carouselId,
                        $carouselId
                    );
                } else {
                    $inner .= '<div class="' . implode(' ', $gridClasses) . '">' . $colsHtml . '</div>';
                }
            }

            // Wrap with container depending on layout
            if ('container-expand' === $layout) {
                $inner = '<div class="container-fluid">' . $inner . '</div>';
            } else {
                $inner = '<div class="container">' . $inner . '</div>';
            }

            $sectionHtml = '<section class="' . implode(' ', $sectionClasses) . '"'
                . ($sectionStyle ? ' style="' . $sectionStyle . '"' : '')
                . '>' . $inner . '</section>';

            $html .= $visibilityClassBefore . $sectionHtml . $visibilityClassAfter;
        }

        return $html;
    }

    protected function mapJustifyContent(?string $justify): string
    {
        return match ($justify) {
            'center' => 'justify-content-center',
            'space-between' => 'justify-content-between',
            'space-around' => 'justify-content-around',
            'flex-start' => 'justify-content-start',
            'flex-end' => 'justify-content-end',
            default => ''
        };
    }

    protected function mapVerticalAlign(?string $align): string
    {
        return match ($align) {
            'top' => 'align-items-start',
            'center' => 'align-items-center',
            'bottom' => 'align-items-end',
            'stretch' => 'align-items-stretch',
            default => ''
        };
    }

    protected function mapHorizontalAlign(?string $align): string
    {
        $align = is_string($align) ? strtolower($align) : '';
        return match ($align) {
            'left' => 'text-start',
            'center', 'centre' => 'text-center',
            'right' => 'text-end',
            default => 'text-start'
        };
    }

    protected function mapWidthToBootstrap(array $widths): string
    {
        $breakpoints = [
            's'  => '-',
            'm'  => '-sm-',
            'l'  => '-md-',
            'xl' => '-lg-',
        ];

        $widthMap = [
            '1-1' => '12',
            '1-2' => '6',
            '1-3' => '4',
            '2-3' => '8',
            '1-4' => '3',
            '3-4' => '9',
            '1-5' => '',
            '1-6' => '2',
        ];

        $classes = [];
        $prevClass = null;

        foreach ($breakpoints as $key => $infix) {
            $width = $widths[$key] ?? null;

            if (null === $width) {
                continue;
            }

            $colNum = $widthMap[$width] ?? '';
            $bsClass = $colNum !== '' ? 'col' . $infix . $colNum : 'col';

            if ($bsClass !== $prevClass) {
                $classes[] = $bsClass;
                $prevClass = $bsClass;
            }
        }

        return count($classes) > 0 ? implode(' ', $classes) : 'col-12';
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

        $parts = explode('-', $size, 2);
        $tag = $parts[0];
        $remaining = $parts[1] ?? '';

        $classNames = $remaining !== '' ? explode('_', $remaining) : [];
        $fontSize = null;

        if (count($classNames) > 0) {
            $lastPart = end($classNames);
            if (is_numeric($lastPart)) {
                $fontSize = $lastPart . 'px';
                array_pop($classNames);
            }
        }

        $classAttr = '';
        if (count($classNames) > 0) {
            $classAttr = ' class="' . htmlspecialchars(implode(' ', $classNames), ENT_QUOTES, 'UTF-8') . '"';
        }

        $styleAttr = '';
        if ($fontSize) {
            $styleAttr = ' style="font-size: ' . htmlspecialchars($fontSize, ENT_QUOTES, 'UTF-8') . ';"';
        }

        $alignment = '';
        if ($block['horizontalAlignment']) {
            $alignment = ' class="' . $this->mapHorizontalAlign($block['horizontalAlignment']) . '"';
        }

        return sprintf('<div%1$s><%2$s%3$s%5$s>%4$s</%2$s></div>', $alignment, $tag, $classAttr, $text, $styleAttr);
    }

    private function renderTextBlock(array $block): string
    {
        $html = (string)($block['content'] ?? '');
        $html = preg_replace('#<script\b[^>]*>(.*?)</script>#is', '', $html) ?? '';

        $replacements = [
            'ql-align-center' => 'text-center',
            'ql-align-right' => 'text-end',
            'ql-align-left' => 'text-start',
            'ql-align-justify' => 'text-justify',
        ];
        $html = str_replace(array_keys($replacements), array_values($replacements), $html);

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
        $class = $block['horizontalAlignment'] ? $this->mapHorizontalAlign($block['horizontalAlignment']) : '';
        $colorClass = $block['class'] ?? 'btn-primary';

        $targetAttribute = '';
        if ($this->isExternalUrl($url)) {
            $targetAttribute = ' target="_blank" rel="noopener noreferrer"';
        }
        return sprintf('<p class="%s"><a href="%s" class="btn %s"%s>%s</a></p>', $class, $url, $colorClass, $targetAttribute, $label);
    }

    private function renderIframeBlock(array $block): string
    {
        $content = (string)($block['content'] ?? '');
        $renderingMode = $block['renderingMode'] ?? 'normal';

        if ($renderingMode === 'ratio169') {
            return sprintf('<div class="ratio ratio-16x9">%s</div>', $content);
        }

        return sprintf('<div class="w-100">%s</div>', $content);
    }

    private function renderBanner(array $block): string
    {
        $text = $block['content'] ?? '';
        return sprintf('<div class="scrolling-content">%s</div>', $text);
    }

    protected function isExternalUrl(string $url): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return true;
        }

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
        return '<hr style="margin-top:' . $mt . 'px;margin-bottom:' . $mb . 'px" />';
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
            $alignment = ' ' . $this->mapHorizontalAlign($block['horizontalAlignment']);
        }

        return sprintf('<div class="%s"><img src="%s" alt="%s"%s/></div>', trim($alignment), $this->escapeText($src), $alt, $style);
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
            $html = $this->twig->render('@AropixelPage/builder/modules/_slider_bootstrap.html.twig', [
                'items' => $items,
                'carouselId' => 'carousel-' . substr(md5(uniqid('', true)), 0, 8),
            ]);
            return $html ?: '';
        } catch (\Throwable) {
            return '';
        }
    }

    private function renderGrid(array $block): string
    {
        $row = $block['row'];
        if (empty($row)) {
            return '';
        }

        $html = '<div class="row row-cols-auto justify-content-center text-center">';
        foreach ($row['columns'] as $col) {

            $style = '';
            $colClasses = ['col'];
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
