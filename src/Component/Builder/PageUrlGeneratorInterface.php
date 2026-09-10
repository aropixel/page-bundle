<?php

namespace Aropixel\PageBundle\Component\Builder;

/**
 * Turn a page reference stored in a builder payload into a front-office URL.
 *
 * The bundle cannot know how the application routes its pages: the route name, its parameters and
 * whether the parent slug belongs in the path are all application decisions. The default
 * implementation ({@see RoutePageUrlGenerator}) covers the usual cases through configuration;
 * replace the service to cover anything else — per-host URLs in a multi-tenant application, for
 * instance.
 */
interface PageUrlGeneratorInterface
{
    /**
     * @param array<string, mixed> $data A builder column or block payload. Carries at least
     *                                   `pagePath` (the target page slug) and, when the target has
     *                                   a parent, `parentSlug`.
     *
     * @return string|null The URL, or null when the payload references no page or the URL cannot
     *                     be built. Returning null must never break a page save: callers fall back
     *                     to the raw `url` value.
     */
    public function generate(array $data): ?string;
}
