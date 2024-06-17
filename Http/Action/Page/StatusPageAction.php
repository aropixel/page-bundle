<?php

namespace Aropixel\PageBundle\Http\Action\Page;

use Aropixel\AdminBundle\Infrastructure\Status;
use Aropixel\PageBundle\Entity\Page;
use Symfony\Component\HttpFoundation\Response;

class StatusPageAction
{
    public function __construct(
        private readonly Status $status,
    ){}

    public function __invoke(Page $page) : Response
    {
        $this->status->changeStatus($page);
        return new Response('OK', Response::HTTP_OK);
    }

}
