<?php

namespace Aropixel\PageBundle\Component\Security;

use Aropixel\PageBundle\Entity\PageInterface;

/**
 * Default {@see PageAccessCheckerInterface}: every page is everyone's.
 *
 * Correct for a single-tenant application, where reaching the admin is the authorisation. Replace
 * the service as soon as pages belong to something narrower than the installation.
 */
class AllowAllPageAccessChecker implements PageAccessCheckerInterface
{
    public function isGranted(string $attribute, PageInterface $page): bool
    {
        return true;
    }
}
