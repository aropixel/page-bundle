<?php

namespace Aropixel\PageBundle\Controller\Default;

use Aropixel\AdminBundle\Component\Translation\TranslationResolverInterface;
use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Entity\PageInterface;
use Aropixel\PageBundle\Form\Type\DefaultPageType;
use Aropixel\PageBundle\Form\Type\DefaultTranslatablePageType;
use Aropixel\PageBundle\Form\Type\PageFormTypeInterface;
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
     * @param string $type The type of page to create (defaults to 'default').
     */
    public function __invoke(string $type = Page::TYPE_DEFAULT): Response
    {
        $isTranslatable = $this->translationResolver->isTranslatable();

        $entities = $this->getParameter('aropixel_page.entities');
        $forms = $this->getParameter('aropixel_page.forms');
        $entityName = $entities[PageInterface::class];

        $page = new $entityName();
        $page->setType($type);

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
            'isTranslatable' => $isTranslatable,
        ]);
    }
}
