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
class EditPageAction extends AbstractController
{
    /**
     * @param int $id The ID of the page to edit.
     * @return Response
     */
    public function __invoke(int $id) : Response
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
            return $this->redirectToRoute('aropixel_page_edit', ['type' => $page->getType(), 'id' => $page->getId()]);
        }

        return $this->render(sprintf('@AropixelPage/default%s/form.html.twig', $isTranslatable ? '_translatable' : ''), [
            'page' => $page,
            'form' => $form->createView()
        ]);
    }
}
