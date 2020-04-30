<?php

namespace Aropixel\PageBundle\Controller;

use Aropixel\AdminBundle\Services\Status;
use Aropixel\PageBundle\Block\BlockManager;
use Aropixel\PageBundle\Entity\Block;
use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Form\PageType;
use Aropixel\PageBundle\Repository\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/page")
 */
class PageController extends AbstractController
{
    /**
     * @Route("/", name="page_index", methods={"GET"})
     */
    public function index(PageRepository $pageRepository): Response
    {
        //
        $pages = $pageRepository->findNotPreset();

        //
        $delete_forms = array();
        foreach ($pages as $entity) {
            $deleteForm = $this->createDeleteForm($entity);
            $delete_forms[$entity->getId()] = $deleteForm->createView();
        }

        //
        return $this->render('@AropixelPage/page/index.html.twig', [
            'pages' => $pages,
            'delete_forms' => $delete_forms,
        ]);
    }

    /**
     * @Route("/new", name="page_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $page = new Page();
        $page->setIsPageTitleEnabled(true);
        $page->setIsPageExcerptEnabled(true);
        $page->setIsPageDescriptionEnabled(true);
        $page->setIsPageImageEnabled(true);
        $page->setIsPresetPage(false);

        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($page);
            $entityManager->flush();


            $this->addFlash('notice', 'La page a bien été enregistrée.');
            return $this->redirectToRoute('page_edit', array('id' => $page->getId()));
        }

        return $this->render('@AropixelPage/page/form.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{id}/edit", name="page_edit", methods={"GET","POST"})
     */
    public function edit(
        Request $request,
        Page $page,
        BlockManager $blockManager
    ): Response
    {

        // clean les blocks supprimés / modifiés en config
        $blockManager->cleanDeletedBlocksByPage($page);
        // persist les nouveaux blocs de la config
        $blockManager->persistBlocksByPage($page);

        $em = $this->getDoctrine()->getManager();
        $image = $page->getImage();

        $auth_checker = $this->get('security.authorization_checker');
        $isRoleAdmin = $auth_checker->isGranted('ROLE_HYPER_ADMIN');

        $deleteForm = $this->createDeleteForm($page);
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($image && $page->getImage()->getImage()==null) {
                $em->remove($image);
            }

            $this->addFlash('notice', 'La page a bien été enregistrée.');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('page_edit', array('id' => $page->getId()));

        }

        return $this->render('@AropixelPage/page/form.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
            'delete_form' => ((!$page->getCode() || $isRoleAdmin) ? $deleteForm->createView() : false)
        ]);
    }


    /**
     * @Route("/{id}/status", name="page_status", methods={"GET"})
     */
    public function statusAction(Page $page, Status $status)
    {
        return $status->changeStatus($page);

    }


    /**
     * @Route("/{id}", name="page_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Page $page): Response
    {
        $titre = $page->getTitle();
        $form = $this->createDeleteForm($page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($page);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('notice', 'La page "'.$titre.'" a bien été supprimé.');
        }

        return $this->redirectToRoute('page_index');
    }

    /**
     * Creates a form to delete the entity.
     * @return FormInterface The form
     */
    private function createDeleteForm(Page $page)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('page_delete', array('id' => $page->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
