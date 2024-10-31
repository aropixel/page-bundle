<?php

namespace Aropixel\PageBundle\Http\Action\Page;

use Aropixel\AdminBundle\Entity\Publishable;
use Aropixel\PageBundle\Entity\PageInterface;
use Aropixel\PageBundle\Form\FormFactoryInterface;
use Aropixel\PageBundle\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class CreateUniquePageAction extends AbstractController
{
    public function __construct(
        private readonly FormFactoryInterface $factory,
        private readonly PageRepository $pageRepository,
        private readonly RequestStack $request,
    ){}

    public function __invoke(string $type) : Response
    {
        $page = $this->pageRepository->findOneBy(['type' => $type]);

        if (!$page) {

            $entities = $this->getParameter('aropixel_page.entities');
            $entityName = $entities[PageInterface::class];

            /** @var PageInterface $page */
            $page = new $entityName();
            $page->setStatus(Publishable::STATUS_ONLINE);
            $page->setType($type);
        }

        $form = $this->factory->createForm($page);
        $form->handleRequest($this->request->getMainRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->pageRepository->add($page, true);

            $this->addFlash('notice', 'La page a bien été enregistrée.');
            return $this->redirectToRoute('aropixel_page', ['type' => $page->getType(), 'id' => $page->getId()]);
        }

        return $this->render($this->factory->getTemplatePath().'/'.$type.'/form.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
        ]);

    }
}
