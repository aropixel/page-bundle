<?php

namespace Aropixel\PageBundle\Controller\Default;

use Aropixel\AdminBundle\Component\Translation\TranslationResolverInterface;
use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Entity\PageInterface;
use Aropixel\PageBundle\Form\Type\DefaultPageType;
use Aropixel\PageBundle\Form\Type\DefaultTranslatablePageType;
use Aropixel\PageBundle\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the creation of a new page.
 */
class CreateAction extends AbstractController
{
    public function __construct(
        private readonly PageRepository $pageRepository,
        private readonly RequestStack $request,
        private readonly TranslationResolverInterface $translationResolver
    ) {
    }

    /**
     * @param string $type The page type (standard: 'default').
     * @return Response
     */
    public function __invoke(string $type) : Response
    {
        $isTranslatable = $this->translationResolver->isTranslatable();

        $entities = $this->getParameter('aropixel_page.entities');
        $entityName = $entities[PageInterface::class];


        $page = new $entityName();
        $page->setType(Page::TYPE_DEFAULT);

        $form = $this->createForm($isTranslatable ?
            DefaultTranslatablePageType::class :
            DefaultPageType::class,
            $page
        );
        $form->handleRequest($this->request->getMainRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->pageRepository->add($page, true);

            $this->addFlash('notice', 'La page a bien été enregistrée.');
            return $this->redirectToRoute('aropixel_page_edit', ['type' => $page->getType(), 'id' => $page->getId()]);
        }

        return $this->render(sprintf('@AropixelPage/default%s/form.html.twig', $isTranslatable ? '_translatable' : ''), [
            'page' => $page,
            'form' => $form->createView(),
            'isTranslatable' => $isTranslatable
        ]);

    }

}
