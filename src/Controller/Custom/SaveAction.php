<?php

namespace Aropixel\PageBundle\Controller\Custom;

use Aropixel\AdminBundle\Entity\Publishable;
use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Entity\PageTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Save custom page data via API.
 */
class SaveAction extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
            }

            $id = $data['id'] ?? null;
            $locale = $data['locale'] ?? 'fr';

            // Récupération ou création
            if ($id) {
                $page = $this->entityManager->getRepository(Page::class)->find($id);
                if (!$page) {
                    return new JsonResponse(['error' => 'Page not found'], Response::HTTP_NOT_FOUND);
                }
            } else {
                $page = new Page();
                $page->setType(Page::TYPE_CUSTOM);
                $page->setStatus(Publishable::STATUS_OFFLINE);
                $this->entityManager->persist($page);
                // Premier flush pour obtenir l'ID, nécessaire aux lookups de traduction
                $this->entityManager->flush();
            }

            // Champs directs (colonne principale = fallback quand pas de traduction)
            if (isset($data['title'])) {
                $page->setTitle($data['title']);
            }
            if (isset($data['slug']) && $data['slug'] !== '') {
                $page->setSlug($data['slug']);
            }
            if (isset($data['status'])) {
                $page->setStatus($data['status']);
            }
            if (isset($data['metaTitle'])) {
                $page->setMetaTitle($data['metaTitle']);
            }
            if (isset($data['description'])) {
                $page->setMetaDescription($data['description']);
            }
            if (isset($data['content'])) {
                $contentToSave = is_array($data['content']) ? $data['content'] : json_decode($data['content'], true);
                $page->setJsonContent(json_encode($contentToSave));
            }

            // Traductions : alimente aropixel_page_translation avec les bons noms de propriété
            // que getTranslation() recherche dans la collection.
            // Mapping clé JS → nom de propriété PHP (= valeur de PageTranslation.field)
            $translatableMap = [
                'title'       => 'title',
                'slug'        => 'slug',
                'metaTitle'   => 'metaTitle',
                'description' => 'metaDescription',
                'content'     => 'jsonContent',
            ];

            $translationRepo = $this->entityManager->getRepository(PageTranslation::class);

            foreach ($translatableMap as $jsKey => $entityField) {
                if (!isset($data[$jsKey])) {
                    continue;
                }

                $value = $data[$jsKey];
                if ($jsKey === 'content') {
                    $value = is_array($value) ? json_encode($value) : $value;
                }

                $translation = $translationRepo->findOneBy([
                    'object' => $page,
                    'locale' => $locale,
                    'field'  => $entityField,
                ]);

                if ($translation) {
                    $translation->setContent($value);
                } else {
                    $translation = new PageTranslation($locale, $entityField, $value);
                    $page->addTranslation($translation);
                    $this->entityManager->persist($translation);
                }
            }

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'id'      => $page->getId(),
                'slug'    => $page->getSlug(),
                'status'  => $page->getStatus(),
                'message' => 'Page enregistrée avec succès.',
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error'   => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
