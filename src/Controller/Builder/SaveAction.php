<?php

namespace Aropixel\PageBundle\Controller\Builder;

use Aropixel\AdminBundle\Entity\Publishable;
use Aropixel\PageBundle\Component\Builder\BlockPolicy;
use Aropixel\PageBundle\Component\Builder\PageBuilderRendererInterface;
use Aropixel\PageBundle\Component\Security\PageAccessCheckerInterface;
use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Entity\PageTranslation;
use Aropixel\PageBundle\Event\PageSavedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Save builder page data via API.
 */
class SaveAction extends AbstractController
{
    public function __construct(
        private readonly PageAccessCheckerInterface $accessChecker,
        private readonly EntityManagerInterface $entityManager,
        private readonly PageBuilderRendererInterface $renderer,
        private readonly BlockPolicy $blockPolicy,
        private readonly EventDispatcherInterface $eventDispatcher,
        #[Autowire('%aropixel_page.page_builder.enabled%')]
        private readonly bool $pageBuilderEnabled = true,
        /** @var list<array{type: string, label: string}> */
        #[Autowire('%aropixel_page.page_builder.custom_blocks%')]
        private readonly array $customBlocks = [],
    ) {
    }

    /**
     * Le libellé qu'un bloc porte dans la bibliothèque, à défaut son type.
     */
    private function blockLabel(string $type): string
    {
        foreach ($this->customBlocks as $block) {
            if (($block['type'] ?? null) === $type) {
                return (string) ($block['label'] ?? $type);
            }
        }

        return $type;
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (!$this->pageBuilderEnabled) {
            throw $this->createNotFoundException();
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
            }

            // La bibliothèque ne propose que les blocs autorisés, mais le payload arrive en JSON :
            // c'est ici que la liste blanche est réellement opposable.
            $forbidden = $this->blockPolicy->findForbidden($data['content'] ?? null);
            if ([] !== $forbidden) {
                return new JsonResponse([
                    'success' => false,
                    'error' => \sprintf('Types de blocs non autorisés : %s.', implode(', ', $forbidden)),
                ], Response::HTTP_BAD_REQUEST);
            }

            // Symétrique de la liste blanche : certains blocs ne sont pas une question de goût. Le
            // refus est nommé avec le libellé de la bibliothèque, le type seul ne disant rien à
            // l'exploitant qui vient de supprimer le bloc sans y penser.
            $missing = $this->blockPolicy->findMissing($data['content'] ?? null);
            if ([] !== $missing) {
                return new JsonResponse([
                    'success' => false,
                    'error' => \sprintf(
                        1 === \count($missing)
                            ? 'Le bloc « %s » est obligatoire : il doit rester dans la page.'
                            : 'Ces blocs sont obligatoires et doivent rester dans la page : %s.',
                        implode(', ', array_map($this->blockLabel(...), $missing)),
                    ),
                ], Response::HTTP_BAD_REQUEST);
            }

            $id = $data['id'] ?? null;
            $locale = $data['locale'] ?? 'fr';

            // Récupération ou création
            if ($id) {
                $page = $this->entityManager->getRepository(Page::class)->find($id);
                if (!$page) {
                    return new JsonResponse(['error' => 'Page not found'], Response::HTTP_NOT_FOUND);
                }

                // Charger une page par son identifiant brut ne dit rien du droit de l'écrire.
                if (!$this->accessChecker->isGranted(PageAccessCheckerInterface::EDIT, $page)) {
                    return new JsonResponse(['error' => 'Page not found'], Response::HTTP_NOT_FOUND);
                }
            } else {
                $page = new Page();
                $page->setType(Page::TYPE_BUILDER);
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

            // Rend le JSON → HTML et le stocke dans htmlContent (même pattern que jsonContent)
            $renderedHtml = '';
            if (isset($data['content'])) {
                $jsonContent = is_array($data['content']) ? json_encode($data['content']) : $data['content'];
                $renderedHtml = $this->renderer->render($jsonContent);

                $htmlTranslation = $translationRepo->findOneBy([
                    'object' => $page,
                    'locale' => $locale,
                    'field'  => 'htmlContent',
                ]);

                if ($htmlTranslation) {
                    $htmlTranslation->setContent($renderedHtml);
                } else {
                    $htmlTranslation = new PageTranslation($locale, 'htmlContent', $renderedHtml);
                    $page->addTranslation($htmlTranslation);
                    $this->entityManager->persist($htmlTranslation);
                }
            }

            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(
                new PageSavedEvent($page, $locale, $renderedHtml),
                PageSavedEvent::NAME
            );

            return new JsonResponse([
                'success' => true,
                'id'      => $page->getId(),
                'slug'    => $page->getSlug(),
                'status'  => $page->getStatus(),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error'   => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
