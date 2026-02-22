<?php

namespace Aropixel\PageBundle\Controller\Custom;

use App\Component\Accordion\AccordionRenderer;
use App\Component\Grid\IconBoxRendered;
use App\Component\Lineup\LineupRendered;
use App\Component\PageBuilder\BlogRenderer;
use App\Component\PageBuilder\ContactFormRenderer;
use App\Component\Slider\SliderRendered;
use App\Component\Ticketing\TicketingRenderer;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Render the JSON produced by the admin Page Builder into UIKit-based HTML.
 */
class RenderAction
{
    public function __construct(
        private readonly HttpKernelInterface $httpKernel,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LineupRendered $lineupRendered,
        private readonly Packages $assetPackages,
        private readonly RequestStack $requestStack,
        private readonly AccordionRenderer $accordionRenderer,
        private readonly ContactFormRenderer $contactFormRenderer,
        private readonly BlogRenderer $blogRenderer,
        private readonly TicketingRenderer $ticketRenderer,
        private readonly SliderRendered $sliderRendered,
        private readonly IconBoxRendered $iconBoxRendered,
        private readonly Environment $twig,
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
                    $sectionStyle .= 'background-image:url(' . htmlspecialchars($background['value']) . ');background-size:cover;background-position:center;background-repeat: no-repeat;';
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
                $isTicketing = $row['type'] === 'ticketing';

                // si c'est un icon-box, on récupère directement le twig
                if ($row['type'] === 'icon-box') {
                    $inner .= $this->iconBoxRendered->render($row);
                    continue;
                }

                $rowAlign = $row['align'] ?? null;
                $gridClasses = ['uk-grid'];
                if ($rowAlign) {
                    $gridClasses[] = $this->mapVerticalAlign($rowAlign);
                }

                $rowJustifyContent = $row['justify'] ?? null;
                if ($rowAlign) {
                    $gridClasses[] = $this->mapJustifyContent($rowJustifyContent);
                }

                if ($isTicketing) {
                    $gridClasses[] = 'grid-tickets';
                } else if ($row['type'] === 'collapse') {
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

                    if (null !== $slider && $isTicketing) {
                        $widths['s'] = '2-3';
                    }

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
                    $style = '';
                    if (!empty($col['background']) && is_array($col['background'])) {
                        $bg = $col['background'];
                        if (($bg['type'] ?? null) === 'color' && !empty($bg['value'])) {
                            $style .= 'background-color:' . htmlspecialchars($bg['value']) . ';';
                        } elseif (($bg['type'] ?? null) === 'image' && !empty($bg['value'])) {
                            $style .= 'background-image:url(' . htmlspecialchars($bg['value']) . ');background-size:cover;background-position:center;';
                        } elseif (($bg['type'] ?? null) === 'class' && !empty($bg['value'])) {
                            $colClasses[] = htmlspecialchars($bg['value']);
                        }
                    }

                    /** Si une hauteur est définie, on fait en sorte que le contenu soit centré verticalement */
                    if ($col['height'] && 'auto' !== $col['height']) {
                        $colClasses[] = 'uk-height-' . $col['height'];
                        $colClasses[] = 'uk-flex uk-flex-column uk-flex-center';
                    }

                    $colUrl = $col['url'] ?? null;
                    if (isset($col['linkType']) && $col['linkType'] === 'page' && !empty($col['pagePath'])) {
                        $colUrl = $this->urlGenerator->generate('front_page_show', ['slug' => $col['pagePath']]);
                    }
                    dump($colUrl);
                    $blocksHtml = $colUrl ? '<a href="' . htmlspecialchars($colUrl) . '">' : '';
                    dump($blocksHtml);
                    $blocks = $col['blocks'] ?? [];
                    foreach ($blocks as $block) {
                        $blocksHtml .= $this->renderBlock($block);
                        if ('accordion' === $block['type']) {
                            $sectionClasses[] = 'infos-pratiques';
                        }
                        if ('banner' === $block['type']) {
                            $sectionClasses[] = 'uk-section-small';
                            $sectionClasses[] = 'bkg-img-primary';
                            $sectionClasses[] = 'scrolling-text';
                        }
                        if ('deezer' === $block['type']) {
                            $colClasses[] = 'cta-deezer';
                        }
                    }
                    $blocksHtml .= $colUrl ? '</a>' : '';
                    dump($blocksHtml);

                    $colsHtml .= sprintf(
                        '<div class="%s"%s>%s</div>',
                        implode(' ', array_filter(array_unique($colClasses))),
                        $style ? ' style="' . $style . '"' : '',
                        $blocksHtml
                    );
                }

                if (null !== $slider) {
                    $slider = sprintf('
                        <div class="uk-position-relative uk-visible-toggle">
                            <div class="uk-slider-items %s" uk-grid>%s</div>
                            <a class="uk-position-center-left uk-position-small uk-hidden-hover" href uk-slidenav-previous uk-slider-item="previous"></a>
                            <a class="uk-position-center-right uk-position-small uk-hidden-hover" href uk-slidenav-next uk-slider-item="next"></a>
                        </div>', implode(' ', $gridClasses), $colsHtml);

                    $sliderClass = $isTicketing ? 'slider-tickets' : '';

                    $inner .= '<div uk-slider class="' . $sliderClass .'">' . $slider . '<ul class="uk-slider-nav uk-dotnav uk-flex-center uk-margin-medium-top"></ul></div>';
                } else {
                    $inner .= sprintf('<div class="%s" uk-grid>%s</div>', implode(' ', $gridClasses), $colsHtml);
                }

            }

            // Wrap with container depending on layout
            if ('container-expand' === $layout) {
                $inner = '<div>' . $inner . '</div>';
            } else {
                $layout = $layout !== 'container' ? 'uk-' . $layout : '';
                $inner = sprintf('<div class="uk-container %s">' . $inner . '</div>', $layout);
            }

            if (1 === count($sections)) {
                $sectionClasses[] = 'uk-padding-remove-bottom';
                $assetUrl = $this->assetPackages->getUrl('images/bkg/footer-tiger.webp');
                $inner .= '<div class="footer-tiger uk-background-bottom-right uk-background-cover uk-panel uk-margin-top" style="background-image: url(' . htmlspecialchars($assetUrl) . ');"></div>';
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
            'accordion' => $this->renderAccordionBlock($block),
            'contact-form' => $this->renderContactFormBlock(),
            'blog' => $this->renderBlogBlock(),
            'deezer' => $this->renderDeezerBlock(),
            'ticket' => $this->renderTicketBlock($block),
            'lineup' => $this->renderLineupBlock($block),
            'nested-row' => $this->renderGrid($block),
            'banner' => $this->renderBanner($block),
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
        $class = $block['horizontalAlignment'] ? 'uk-text-' . $block['horizontalAlignment'] : '';
        // content may contain basic HTML; trust it but strip <script>
        $html = (string)($block['content'] ?? '');
        $html = preg_replace('#<script\b[^>]*>(.*?)</script>#is', '', $html) ?? '';

        // Échapper les % pour sprintf en les doublant
        $html = str_replace('%', '%%', $html);

        return sprintf('<p class="%1s">%2s</p>', $class, $html);
    }

    private function renderButtonBlock(array $block): string
    {
        $label = $this->escapeText((string)($block['label'] ?? ''));
        $url = (string)($block['url'] ?? '#');
        $linkType = $block['linkType'] ?? null;
        $pagePath = $block['pagePath'] ?? null;

        if ($linkType === 'page' && !empty($pagePath)) {
            $url = $this->urlGenerator->generate('front_page_show', ['slug' => $pagePath]);
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

    private function renderBanner(array $block): string
    {
        $text = $this->escapeText((string)($block['content'] ?? ''));

        // Remplacer les tirets entourés d'espaces par <span>-</span>
        $text = preg_replace('/\s+-\s+/', ' <span>-</span> ', $text);
        $text .= ' <span>...</span>';

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
        return '<hr class="uk-divider-icon" style="margin-top:' . $mt . 'px;margin-bottom:' . $mb . 'px" />';
    }

    private function renderImageBlock(array $block): string
    {
        $src = (string)($block['src'] ?? '');
        if ($src === '') {
            return '';
        }
        $src = str_replace('admin_preview', 'page', $src);
        $alt = $this->escapeText((string)($block['alt'] ?? ''));
        $useOriginal = (bool)($block['useOriginalSize'] ?? false);
        $width = (int)($block['width'] ?? 100);
        $style = '';
        if (!$useOriginal) {
            $style = ' style="width:' . max(1, min(100, $width)) . '%;height:auto"';
        }
        // Échapper les % pour sprintf en les doublant
        $style = str_replace('%', '%%', $style);
        return sprintf('<div class="uk-inline"><img src="%s" alt="%s"%s/></div>', $this->escapeText($src), $alt, $style);
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
            $alt = $this->escapeText((string)($img['alt'] ?? ''));
            $items[] = ['src' => $src, 'alt' => $alt];
        }

        try {
            $html = $this->sliderRendered->render($items);
            return $html ?: '';
        } catch (\Throwable $e) {
            // Fallback silencieux en cas d'erreur
            return '';
        }
    }

    private function renderAccordionBlock(array $block): string
    {
        $title = $this->escapeText((string)($block['data']['title'] ?? ''));
        $accId = $block['data']['accordionId'] ?? null;
        if (!$accId) {
            return '';
        }
        $out = '';
        if ($title !== '') {
            $out .= '<h3>' . $title . '</h3>';
        }

        // Rendu serveur via sous‑requête vers la preview admin
        try {
            $html = $this->accordionRenderer->renderById((int)$accId, $block);
            $out .= $html ?: '';
        } catch (\Throwable $e) {
            // Fallback silencieux en cas d'erreur
        }

        return $out;
    }

    private function renderContactFormBlock(): string
    {
        // Rendu serveur via sous‑requête vers la preview admin
        try {
            return $this->contactFormRenderer->renderTwig();
        } catch (\Throwable $e) {
            // Fallback silencieux en cas d'erreur
        }

        return '';
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

    private function renderDeezerBlock(): string
    {
        // Rendu serveur via sous‑requête vers la preview admin
        try {
            return $this->twig->render('front/page/modules/_deezer.html.twig');
        } catch (\Throwable $e) {
            // Fallback silencieux en cas d'erreur
        }

        return '';
    }

    private function renderTicketBlock(array $block): string
    {
        $accId = $block['data']['ticketId'] ?? null;
        if (!$accId) {
            return '';
        }
        $out = '';

        // Rendu serveur via sous‑requête vers la preview admin
        try {
            $html = $this->ticketRenderer->renderById((int)$accId, $block);
            $out .= $html ?: '';
        } catch (\Throwable $e) {
            // Fallback silencieux en cas d'erreur
        }

        return $out;
    }

    private function renderLineupBlock(array $block): string
    {
        $lineupId = $block['data']['lineupId'] ?? null;
        if (!$lineupId) {
            return '';
        }
        // Utiliser le service SSR dédié (évite l'appel à une route admin depuis le front)
        $device = 'desktop';
        try {
            $html = $this->lineupRendered->renderById((int)$lineupId, $device);
            return $html ?: '';
        } catch (\Throwable $e) {
            // Fallback silencieux en cas d'erreur
            return '';
        }
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
                    $style .= 'background-image:url(' . htmlspecialchars($bg['value']) . ');background-size:cover;background-position:center;';
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


        //$out = '';

//        $alignItems = $row['alignItems'] ?? 'center';
//        $justifyContent = $row['justifyContent'] ?? 'center';
//        $mobileChildWidth = $row['childrenSizes']['mobile'];
//        $tabletChildWidth = $row['childrenSizes']['tablet'];
//        $desktopChildWidth = $row['childrenSizes']['desktop'];
        //$out .= sprintf('<div class="uk-grid-match uk-child-width-%s uk-child-width-%s@s uk-child-width-%s@m uk-flex-%s uk-text-center uk-grid uk-scrollspy-inview" uk-grid>', $desktopChildWidth, $tabletChildWidth, $mobileChildWidth, $justifyContent);;
    }

    private function fetchAccordionPreview(int $accordionId): string
    {
        // Génère le chemin de la route admin_accordion_preview
        $path = $this->urlGenerator->generate('admin_accordion_preview', ['id' => $accordionId], UrlGeneratorInterface::ABSOLUTE_PATH);

        // Construire une sous‑requête GET
        $subRequest = Request::create($path, 'GET');

        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        if (method_exists($response, 'getStatusCode') && $response->getStatusCode() === 200) {
            return (string) $response->getContent();
        }

        return '';
    }
}
