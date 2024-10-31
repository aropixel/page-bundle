<?php

namespace Aropixel\PageBundle\Http\Action\Page;

use Aropixel\PageBundle\Form\FormFactoryInterface;
use Aropixel\PageBundle\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class EditPageAction extends AbstractController
{
    public function __construct(
        private readonly FormFactoryInterface $factory,
        private readonly PageRepository $pageRepository,
        private readonly RequestStack $request,
    ){}

    public function __invoke(int $id) : Response
    {
        $page = $this->pageRepository->find($id);

        if (!$page) {
            throw $this->createNotFoundException();
        }

        $form = $this->factory->createForm($page);
        $form->handleRequest($this->request->getMainRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->pageRepository->add($page, true);

            $this->addFlash('notice', 'La page a bien été enregistrée.');
            return $this->redirectToRoute('aropixel_page_edit', ['type' => $page->getType(), 'id' => $page->getId()]);
        }

        return $this->render($this->factory->getTemplatePath().'/'.$page->getType().'/form.html.twig', [
            'page' => $page,
            'form' => $form->createView()
        ]);
    }
}
