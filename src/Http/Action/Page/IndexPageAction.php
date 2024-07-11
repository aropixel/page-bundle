<?php

namespace Aropixel\PageBundle\Http\Action\Page;

use Aropixel\PageBundle\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;

class IndexPageAction extends AbstractController
{
    public function __construct(
        private readonly PageRepository $pageRepository,
        private readonly ParameterBagInterface $parameterBag
    ){}

    public function __invoke(string $type) : Response
    {
        $isTranslatable = $this->parameterBag->has('translatable') && $this->parameterBag->get('translatable');

        if ($isTranslatable) {
            $pages = $this->pageRepository->findBy(['type' => $type], ['title' => 'ASC']);
        } else {
            $pages = $this->pageRepository->findBy(['type' => $type], ['title' => 'ASC']);
        }

        return $this->render('@AropixelPage/index.html.twig', [
            'type' => $type,
            'pages' => $pages
        ]);
    }

}