<?php

namespace Aropixel\PageBundle\Http\Action\Page;

use Aropixel\AdminBundle\Entity\Publishable;
use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Entity\PageInterface;
use Aropixel\PageBundle\Form\FormFactoryInterface;
use Aropixel\PageBundle\Http\Form\Page\FormFactory;
use Aropixel\PageBundle\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateUniquePageAction extends AbstractController
{
    public function __construct(
        private readonly FormFactory $formFactory,
        private readonly FormFactoryInterface $factory,
        private readonly PageRepository $pageRepository,
        private readonly RequestStack $request,
        private readonly TranslatorInterface $translator,
    ){}

    private string $model = Page::class;

    public function __invoke(string $type) : Response
    {
        $page = $this->pageRepository->findOneBy(['type' => $type]);

        if (!$page) {
            /** @var PageInterface $page */
            $page = new $this->model();
            $page->setStatus(Publishable::STATUS_ONLINE);
            $page->setType($type);
        }

        $form = $this->factory->createForm($page);
        $form->handleRequest($this->request->getMainRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->pageRepository->add($page, true);

            $this->addFlash('notice', $this->translator->trans('The page has been successfully saved.'));
            return $this->redirectToRoute('aropixel_page', array('type' => $page->getType(), 'id' => $page->getId()));
        }

        return $this->render($this->formFactory->getTemplatePath().'/'.$type.'/form.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
        ]);

    }
}