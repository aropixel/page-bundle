<?php

namespace Aropixel\PageBundle\Component\Builder;

use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Default {@see PageUrlGeneratorInterface}: generates a configured route.
 *
 * Configured under `aropixel_page.page_builder.front_route`. Two page URL conventions are covered:
 *
 * - hierarchical — `/{fullPath}`, the parent slug prefixing the page slug (`include_parent: true`);
 * - flat — `/page/{slug}`, the page slug alone (`include_parent: false`).
 *
 * A missing or non-generatable route yields null and a warning rather than an exception: a broken
 * link must not stop an author from saving a page.
 */
class RoutePageUrlGenerator implements PageUrlGeneratorInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $routeName,
        private readonly string $routeParameter,
        private readonly bool $includeParent = true,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function generate(array $data): ?string
    {
        $pagePath = $data['pagePath'] ?? null;

        if (!\is_string($pagePath) || '' === $pagePath) {
            return null;
        }

        $parentSlug = $data['parentSlug'] ?? null;
        $path = $this->includeParent && \is_string($parentSlug) && '' !== $parentSlug
            ? $parentSlug . '/' . $pagePath
            : $pagePath;

        try {
            return $this->urlGenerator->generate(
                $this->routeName,
                [$this->routeParameter => $path],
                UrlGeneratorInterface::RELATIVE_PATH,
            );
        } catch (RoutingExceptionInterface $e) {
            $this->logger?->warning(
                'Page builder: cannot generate the front URL of page "{path}" with route "{route}". '
                . 'Check aropixel_page.page_builder.front_route.',
                ['path' => $path, 'route' => $this->routeName, 'exception' => $e],
            );

            return null;
        }
    }
}
