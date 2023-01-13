<?php

namespace Aropixel\PageBundle\Http\Action\Page;

use Aropixel\AdminBundle\Services\Status;
use Aropixel\PageBundle\Entity\Page;
use Symfony\Component\HttpFoundation\Response;

class StatusPageAction
{
    public function __construct(
        private readonly Status $status,
    ){}

    public function __invoke(Page $page) : Response
    {
        return $this->status->changeStatus($page);
    }

}