<?php

namespace Aropixel\PageBundle\Controller;

use Aropixel\AdminBundle\Component\Status\StatusInterface;
use Aropixel\PageBundle\Component\Security\PageAccessCheckerInterface;
use Aropixel\PageBundle\Entity\Page;
use Symfony\Component\HttpFoundation\Response;

class StatusAction
{
    public function __construct(
        private readonly PageAccessCheckerInterface $accessChecker,
        private readonly StatusInterface $status,
    ) {
    }

    public function __invoke(Page $page): Response
    {
        if (!$this->accessChecker->isGranted(PageAccessCheckerInterface::EDIT, $page)) {
            throw $this->createNotFoundException();
        }

        $this->status->changeStatus($page);

        return new Response('OK', Response::HTTP_OK);
    }
}
