<?php

namespace Aropixel\PageBundle\Controller\Default;

use Aropixel\AdminBundle\Component\Translation\TranslationResolverInterface;
use Aropixel\PageBundle\Form\Type\DefaultPageType;
use Aropixel\PageBundle\Form\Type\DefaultTranslatablePageType;
use Aropixel\PageBundle\Form\Type\PageFormTypeInterface;
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
        $forms = $this->getParameter('aropixel_page.forms');

        $type = $page->getType();
        if (!isset($forms[$type])) {
            throw $this->createNotFoundException(sprintf('The page type "%s" is not defined.', $type));
        }

        $formType = $forms[$type];
        $form = $this->createForm($formType, $page);

        $innerType = $form->getConfig()->getType()->getInnerType();
        if (!$innerType instanceof PageFormTypeInterface) {
            throw new \RuntimeException(sprintf('The form type "%s" must implement PageFormTypeInterface.', get_class($innerType)));
        }

        $form->handleRequest($this->request->getMainRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->pageRepository->add($page, true);

            $this->addFlash('notice', 'La page a bien été enregistrée.');
            return $this->redirectToRoute('aropixel_default_page_edit', ['id' => $page->getId()]);
        }

        $template = $innerType->getTemplate();
        if (!$this->container->get('twig')->getLoader()->exists($template)) {
            throw $this->createNotFoundException(sprintf('The template "%s" cannot be found.', $template));
        }

        return $this->render($template, [
            'page' => $page,
            'form' => $form->createView(),
        ]);
    }
}
