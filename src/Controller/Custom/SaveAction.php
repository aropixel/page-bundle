<?php

namespace Aropixel\PageBundle\Controller\Custom;

use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Entity\PageTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Save custom page data via API.
 */
class SaveAction extends AbstractController
{
    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/page-builder/save', name: 'aropixel_custom_page_save', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
            }

            $id = $data['id'] ?? null;
            $locale = $data['locale'] ?? 'fr';

            // Récupération ou Création
            if ($id) {
                $page = $this->entityManager->getRepository(Page::class)->find($id);
                if (!$page) {
                    return new JsonResponse(['error' => 'Page not found'], Response::HTTP_NOT_FOUND);
                }
            } else {
                $page = new Page();
                $page->setType(Page::TYPE_CUSTOM);
                if (method_exists($page, 'setStatus')) {
                    $page->setStatus('published');
                }
            }

            if (method_exists($page, 'setTranslatableLocale')) {
                $page->setTranslatableLocale($locale);
            }

            $repository = $this->entityManager->getRepository(PageTranslation::class);

            // Liste des champs traduisibles
            $translatableFields = ['title', 'slug', 'description', 'ogImage', 'content'];

            foreach ($translatableFields as $field) {
                if (isset($data[$field])) {
                    $valueToSave = $data[$field];

                    if ($field === 'content' && is_array($valueToSave)) {
                        $valueToSave = json_encode($valueToSave);
                    }

                    $translation = $repository->findOneBy([
                        'object' => $page,
                        'locale' => $locale,
                        'field' => $field
                    ]);

                    if (!$translation) {
                        $translation = new PageTranslation($locale, $field, $valueToSave);
                        $translation->setObject($page);
                        $this->entityManager->persist($translation);
                        $page->addTranslation($translation);
                    } else {
                        $translation->setContent($valueToSave);
                    }
                }
            }


            // Hydratation des champs
            if (isset($data['title'])) {
                $page->setTitle($data['title']);
            }
            if (isset($data['subtitle'])) {
                $page->setSubtitle($data['subtitle']);
            }
            if (isset($data['metaTitle'])) {
                $page->setMetaTitle($data['metaTitle']);
            }
            if (isset($data['status'])) {
                $page->setStatus($data['status']);
            }
            if (!empty($data['slug'])) {
                $page->setSlug($data['slug']);
            }
            if (isset($data['description'])) {
                $page->setDescription($data['description']);
            }
            if (isset($data['ogImage'])) {
                $page->setOgImage($data['ogImage']);
            }
            if (isset($data['content'])) {
                $contentToSave = is_array($data['content']) ? $data['content'] : json_decode($data['content'], true);
                $page->setJsonContent(json_encode($contentToSave));
            }

            // Enregistrement
            $this->entityManager->persist($page);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'id' => $page->getId(),
                'slug' => $page->getSlug(),
                'status' => $page->getStatus(),
                'message' => 'Page enregistrée avec succès.'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
