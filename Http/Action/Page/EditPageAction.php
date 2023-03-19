<?php

namespace Aropixel\PageBundle\Http\Action\Page;

use Aropixel\PageBundle\Entity\PageInterface;
use Aropixel\PageBundle\Form\FormFactoryInterface;
use Aropixel\PageBundle\Http\Form\Page\FormFactory;
use Aropixel\PageBundle\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditPageAction extends AbstractController
{
    private FormFactory $formFactory;
    private FormFactoryInterface $factory;
    private PageRepository $pageRepository;
    private RequestStack $request;
    private Security $security;
    private TranslatorInterface $translator;

    /**
     * @param FormFactory $formFactory
     * @param FormFactoryInterface $factory
     * @param PageRepository $pageRepository
     * @param RequestStack $request
     * @param Security $security
     * @param TranslatorInterface $translator
     */
    public function __construct(FormFactory $formFactory, FormFactoryInterface $factory, PageRepository $pageRepository, RequestStack $request, Security $security, TranslatorInterface $translator)
    {
        $this->formFactory = $formFactory;
        $this->factory = $factory;
        $this->pageRepository = $pageRepository;
        $this->request = $request;
        $this->security = $security;
        $this->translator = $translator;
    }


    public function __invoke(int $id) : Response
    {
        $page = $this->pageRepository->find($id);

        if (!$page) {
            throw $this->createNotFoundException();
        }

        $isRoleAdmin = $this->security->isGranted('ROLE_HYPER_ADMIN');

        $deleteForm = $this->formFactory->createDeleteForm($page);
        $form = $this->factory->createForm($page);
        $form->handleRequest($this->request->getMainRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->pageRepository->add($page, true);

            $this->addFlash('notice', $this->translator->trans('The page has been successfully saved.'));
            return $this->redirectToRoute('aropixel_page_edit', array('type' => $page->getType(), 'id' => $page->getId()));
        }
        return $this->render($this->formFactory->getTemplatePath().'/'.$page->getType().'/form.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
            'delete_form' => ((!$page->getType() == 'default' || $isRoleAdmin) ? $deleteForm->createView() : false)
        ]);



    }
}