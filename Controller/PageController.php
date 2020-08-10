<?php

namespace Aropixel\PageBundle\Controller;

use Aropixel\AdminBundle\Entity\Publishable;
use Aropixel\AdminBundle\Services\Status;
use Aropixel\PageBundle\Block\BlockManager;
use Aropixel\PageBundle\Entity\Block;
use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Entity\PageInterface;
use Aropixel\PageBundle\Form\FormFactoryInterface;
use Aropixel\PageBundle\Form\PageType;
use Aropixel\PageBundle\Repository\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/page")
 */
class PageController extends AbstractController
{

    /** @var ParameterBagInterface */
    private $parameterBag;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var string  */
    private $model;

    /** @var string  */
    private $form;


    /**
     * PageController constructor.
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag, FormFactoryInterface $formFactory)
    {
        $this->parameterBag = $parameterBag;
        $this->formFactory = $formFactory;

        $entities = $parameterBag->get('aropixel_page.entities');
        $forms = $parameterBag->get('aropixel_page.forms');

        $this->model = $entities[PageInterface::class];
//        $this->form = $forms[PageInterface::class];
    }


    /**
     * @Route("/{type}/list", name="aropixel_page_index", methods={"GET","POST"})
     */
    public function index(PageRepository $pageRepository, $type): Response
    {
        //
        $pages = $pageRepository->findBy(['type' => $type], ['title' => 'ASC']);

        //
        $delete_forms = array();
        foreach ($pages as $entity) {
            $deleteForm = $this->createDeleteForm($entity);
            $delete_forms[$entity->getId()] = $deleteForm->createView();
        }

        //
        return $this->render('@AropixelPage/index.html.twig', [
            'type' => $type,
            'pages' => $pages,
            'delete_forms' => $delete_forms,
        ]);
    }


    /**
     * @Route("/{type}/page", name="aropixel_page", methods={"GET","POST"})
     */
    public function page(Request $request, $type): Response
    {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository($this->model)->findOneBy(['type' => $type]);

        if (!$page) {

            /** @var PageInterface $page */
            $page = new $this->model();
            $page->setStatus(Publishable::STATUS_ONLINE);
            $page->setType($type);

        }

        $form = $this->formFactory->createForm($page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();

            $this->addFlash('notice', 'La page a bien été enregistrée.');
            return $this->redirectToRoute('aropixel_page', array('type' => $type));
        }


        return $this->render($this->getTemplatePath().'/'.$type.'/form.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{type}/new", name="aropixel_page_new", methods={"GET","POST"})
     */
    public function new(Request $request, $type): Response
    {
        /** @var PageInterface $page */
        $page = new $this->model();
        $page->setType($type);

        $form = $this->formFactory->createForm($page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();

            $this->addFlash('notice', 'La page a bien été enregistrée.');
            return $this->redirectToRoute('aropixel_page_edit', array('type' => $page->getType(), 'id' => $page->getId()));
        }


        return $this->render($this->getTemplatePath().'/'.$type.'/form.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{type}/{id}/edit", name="aropixel_page_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, $id): Response
    {

        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository($this->model)->find($id);
        if (!$page) {
            throw $this->createNotFoundException();
        }

        $auth_checker = $this->get('security.authorization_checker');
        $isRoleAdmin = $auth_checker->isGranted('ROLE_HYPER_ADMIN');

        $deleteForm = $this->createDeleteForm($page);
        $form = $this->formFactory->createForm($page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash('notice', 'La page a bien été enregistrée.');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('aropixel_page_edit', array('type' => $page->getType(), 'id' => $page->getId()));

        }

        return $this->render($this->getTemplatePath().'/'.$page->getType().'/form.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
            'delete_form' => ((!$page->getType() == 'default' || $isRoleAdmin) ? $deleteForm->createView() : false)
        ]);
    }


    /**
     * @Route("/{id}/status", name="aropixel_page_status", methods={"GET"})
     */
    public function status(Page $page, Status $status)
    {
        return $status->changeStatus($page);

    }


    /**
     * @Route("/{id}", name="aropixel_page_delete", methods={"DELETE"})
     */
    public function delete(Request $request, $id): Response
    {

        /** @var Page $page */
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository($this->model)->find($id);
        if (!$page) {
            throw $this->createNotFoundException();
        }

        $titre = $page->getTitle();
        $form = $this->createDeleteForm($page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($page);
            $em->flush();

            $this->get('session')->getFlashBag()->add('notice', 'La page "'.$titre.'" a bien été supprimé.');
        }

        return $this->redirectToRoute('aropixel_page_index');
    }

    /**
     * Creates a form to delete the entity.
     * @return FormInterface The form
     */
    private function createDeleteForm(Page $page)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('aropixel_page_delete', array('id' => $page->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }

    private function getTemplatePath()
    {
        $formsConfig = $this->getParameter('aropixel_page.forms');
        return $formsConfig['template_path'];
    }
}
