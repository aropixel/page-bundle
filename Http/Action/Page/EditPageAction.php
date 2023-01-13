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

class EditPageAction extends AbstractController
{
    public function __construct(
        private readonly FormFactory $formFactory,
        private readonly FormFactoryInterface $factory,
        private readonly PageRepository $pageRepository,
        private readonly RequestStack $request,
        private readonly Security $security,
    ){}

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

            $this->addFlash('notice', 'La page a bien été enregistrée.');
            return $this->redirectToRoute('aropixel_page_edit', array('type' => $page->getType(), 'id' => $page->getId()));
        }
        return $this->render($this->formFactory->getTemplatePath().'/'.$page->getType().'/form.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
            'delete_form' => ((!$page->getType() == 'default' || $isRoleAdmin) ? $deleteForm->createView() : false)
        ]);



    }
}