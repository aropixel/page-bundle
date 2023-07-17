<?php

namespace Aropixel\PageBundle\Http\Action\Page;

use Aropixel\PageBundle\Entity\PageInterface;
use Aropixel\PageBundle\Form\FormFactoryInterface;
use Aropixel\PageBundle\Http\Form\Page\FormFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Aropixel\PageBundle\Repository\PageRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class CreatePageAction extends AbstractController
{
    public function __construct(
        private readonly FormFactory $formFactory,
        private readonly FormFactoryInterface $factory,
        private readonly RequestStack $request,
        private readonly PageRepository $pageRepository,

    ){}

    public function __invoke(string $type) : Response
    {

        $entities = $this->getParameter('aropixel_page.entities');
        $entityName = $entities[PageInterface::class];

        $page = new $entityName();
        $page->setType($type);

        $form = $this->factory->createForm($page);
        $form->handleRequest($this->request->getMainRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->pageRepository->add($page, true);

            $this->addFlash('notice', 'La page a bien été enregistrée.');
            return $this->redirectToRoute('aropixel_page_edit', ['type' => $page->getType(), 'id' => $page->getId()]);
        }

        return $this->render($this->formFactory->getTemplatePath().'/'.$type.'/form.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
        ]);

    }

}