<?php

namespace Aropixel\PageBundle\Controller\Default;

use Aropixel\AdminBundle\Component\Status\StatusInterface;
use Aropixel\PageBundle\Entity\Page;
use Symfony\Component\HttpFoundation\Response;

class StatusPageAction
{
    public function __construct(
        private readonly StatusInterface $status,
    ) {
    }

    public function __invoke(Page $page) : Response
    {
        $this->status->changeStatus($page);
        return new Response('OK', Response::HTTP_OK);
    }

}
