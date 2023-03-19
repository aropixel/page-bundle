<?php

namespace Aropixel\PageBundle\Http\Action\Page;

use Aropixel\PageBundle\Http\Form\Page\FormFactory;
use Aropixel\PageBundle\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeletePageAction extends AbstractController
{
    private FormFactory $formFactory;
    private PageRepository $pageRepository;
    private RequestStack $request;
    private TranslatorInterface $translator;

    /**
     * @param FormFactory $formFactory
     * @param PageRepository $pageRepository
     * @param RequestStack $request
     * @param TranslatorInterface $translator
     */
    public function __construct(FormFactory $formFactory, PageRepository $pageRepository, RequestStack $request, TranslatorInterface $translator)
    {
        $this->formFactory = $formFactory;
        $this->pageRepository = $pageRepository;
        $this->request = $request;
        $this->translator = $translator;
    }


    public function __invoke(int $id) : Response
    {
        $page = $this->pageRepository->find($id);
        $type = $page->getType();

        $form = $this->formFactory->createDeleteForm($page);
        $form->handleRequest($this->request->getMainRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->pageRepository->remove($page, true);

            $this->addFlash('notice', $this->translator->trans('The page has been successfully deleted.'));
        }

        return $this->redirectToRoute('aropixel_page_index', ['type' => $type]);

    }
}