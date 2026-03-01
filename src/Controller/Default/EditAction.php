<?php

namespace Aropixel\PageBundle\Controller\Default;

use Aropixel\AdminBundle\Component\Translation\TranslationResolverInterface;
use Aropixel\PageBundle\Form\Type\DefaultPageType;
use Aropixel\PageBundle\Form\Type\DefaultTranslatablePageType;
use Aropixel\PageBundle\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the edition of an existing page.
 */
class EditAction extends AbstractController
{
    public function __construct(
        private readonly PageRepository $pageRepository,
        private readonly RequestStack $request,
        private readonly TranslationResolverInterface $translationResolver,
    ) {
    }

    /**
     * @param int $id the ID of the page to edit
     */
    public function __invoke(int $id): Response
    {
        $page = $this->pageRepository->find($id);
        if (!$page) {
            throw $this->createNotFoundException();
        }

        $isTranslatable = $this->translationResolver->isTranslatable();
        $form = $this->createForm($isTranslatable ?
            DefaultTranslatablePageType::class :
            DefaultPageType::class,
            $page
        );
        $form->handleRequest($this->request->getMainRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->pageRepository->add($page, true);

            $this->addFlash('notice', 'La page a bien été enregistrée.');

            return $this->redirectToRoute('aropixel_default_page_edit', ['type' => $page->getType(), 'id' => $page->getId()]);
        }

        return $this->render('@AropixelPage/default/form.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
        ]);
    }
}
