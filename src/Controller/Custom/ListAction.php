<?php

namespace Aropixel\PageBundle\Controller\Custom;

use Aropixel\PageBundle\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * List custom pages.
 */
#[IsGranted('ROLE_CONTENT_EDITOR')]
class ListAction extends AbstractController
{
    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route("/page-builder/list", name: "aropixel_custom_page_list", methods: ["GET"])]
    public function __invoke(): Response
    {
        $pages = $this->entityManager->getRepository(Page::class)->findCustomPages();

        return $this->render('@AropixelPage/custom/list.html.twig', [
            'pages' => $pages,
        ]);
    }
}
