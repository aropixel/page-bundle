<?php

namespace Aropixel\PageBundle\Component\Security;

use Aropixel\PageBundle\Entity\PageInterface;

/**
 * Whether the current user may act on a given page.
 *
 * The bundle loads pages by id, straight from the request: nothing in it knows that an application
 * may hold pages the current user has no business reading or writing — one tenant's pages in a
 * multi-tenant install, for instance. Implement this and replace the service to say so; the default
 * ({@see AllowAllPageAccessChecker}) grants everything, which is what a single-tenant application
 * wants.
 *
 * Denial is reported as 404, not 403: whether a page exists is itself information.
 */
interface PageAccessCheckerInterface
{
    /**
     * Reading a page: the builder canvas, the preview, a listing entry.
     */
    public const VIEW = 'view';

    /**
     * Writing a page: saving builder content, editing fields, changing publication status.
     */
    public const EDIT = 'edit';

    /**
     * Removing a page.
     */
    public const DELETE = 'delete';

    /**
     * @param self::VIEW|self::EDIT|self::DELETE $attribute
     */
    public function isGranted(string $attribute, PageInterface $page): bool;
}
