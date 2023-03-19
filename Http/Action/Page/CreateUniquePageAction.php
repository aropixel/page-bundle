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
    private FormFactory $formFactory;
    private FormFactoryInterface $factory;
    private PageRepository $pageRepository;
    private RequestStack $request;
    private TranslatorInterface $translator;

    private string $model = Page::class;


    /**
     * @param FormFactory $formFactory
     * @param FormFactoryInterface $factory
     * @param PageRepository $pageRepository
     * @param RequestStack $request
     * @param TranslatorInterface $translator
     */
    public function __construct(FormFactory $formFactory, FormFactoryInterface $factory, PageRepository $pageRepository, RequestStack $request, TranslatorInterface $translator)
    {
        $this->formFactory = $formFactory;
        $this->factory = $factory;
        $this->pageRepository = $pageRepository;
        $this->request = $request;
        $this->translator = $translator;
    }


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