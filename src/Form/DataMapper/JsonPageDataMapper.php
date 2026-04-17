<?php

namespace Aropixel\PageBundle\Form\DataMapper;

use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormInterface;

class JsonPageDataMapper implements DataMapperInterface
{
    /**
     * Translatable entity fields that must be written directly to the
     * PersonalTranslation collection (aropixel_page_translation) for the
     * current locale, instead of being stored inside jsonContent.
     *
     * They are read back via the entity's getters (which call getTranslation()).
     *
     * @var list<string>
     */
    private const TRANSLATABLE_ENTITY_FIELDS = ['title', 'slug', 'metaTitle', 'metaDescription', 'metaKeywords'];

    /**
     * @param string|null $slugSource
     *   The form field name whose value also feeds $page->setTitle() so that
     *   Gedmo Sluggable has a non-null source when generating the entity-column
     *   slug. Defaults to 'title'. Override with e.g. 'name' when your form
     *   does not have a 'title' field. Set to null to disable.
     * @param string      $currentLocale   Locale of the current request (e.g. 'fr').
     * @param string      $translationClass FQCN of the PersonalTranslation entity
     *                                      (e.g. PageTranslation::class).
     */
    public function __construct(
        private readonly ?string $slugSource = 'title',
        private readonly string $currentLocale = 'en',
        private readonly string $translationClass = 'Aropixel\PageBundle\Entity\PageTranslation',
    ) {
    }

    /**
     * @param mixed $data
     * @param \Traversable|FormInterface[] $forms
     */
    public function mapDataToForms(mixed $data, \Traversable $forms): void
    {
        if (null === $data) {
            return;
        }

        $jsonContent = $data->getJsonContent();
        $values = $jsonContent ? json_decode($jsonContent, true) : [];

        /** @var FormInterface[] $forms */
        foreach ($forms as $name => $form) {
            if ($this->isTranslatableField($form)) {
                // TranslatableMapper reads the full translations collection and
                // extracts the relevant field/locale values itself.
                $form->setData($data->getTranslations());
                continue;
            }

            // Translatable entity fields are read via the entity getter, which
            // delegates to getTranslation() → reads from the PageTranslation
            // collection for the current locale.
            if (in_array($name, self::TRANSLATABLE_ENTITY_FIELDS, true)) {
                $getter = 'get'.ucfirst($name);
                if (method_exists($data, $getter)) {
                    $form->setData($this->denormalizeValue($data->$getter(), $form));
                }
                continue;
            }

            if (isset($values[$name])) {
                $form->setData($this->denormalizeValue($values[$name], $form));
            }
        }
    }

    /**
     * @param \Traversable|FormInterface[] $forms
     * @param mixed $data
     */
    public function mapFormsToData(\Traversable $forms, mixed &$data): void
    {
        if (null === $data) {
            return;
        }

        $values = [];
        /** @var FormInterface[] $forms */
        foreach ($forms as $name => $form) {
            $innerType = $form->getConfig()->getType()->getInnerType();
            $blockPrefix = $innerType->getBlockPrefix();

            if ('aropixel_admin_translatable' === $blockPrefix) {
                // TranslatableMapper already mutated $data->translations in place
                // during form submission — nothing to do here.
                continue;
            }

            // Translatable entity fields: write directly to the PageTranslation
            // collection for the current locale so data lands in
            // aropixel_page_translation, not only in aropixel_page.
            if (in_array($name, self::TRANSLATABLE_ENTITY_FIELDS, true)) {
                $value = $this->normalizeValue($form->getData());
                $strValue = (null !== $value && '' !== $value) ? (string) $value : null;
                $this->upsertTranslation($data, $name, $strValue);

                // 'title' must also be set on the entity property so Gedmo Sluggable
                // has a non-null source for the aropixel_page.slug column (NOT NULL).
                // All other translatable fields are nullable in the column — Gedmo
                // Translatable will manage them; we only need the translation row.
                if ('title' === $name && null !== $strValue && method_exists($data, 'setTitle')) {
                    $data->setTitle($strValue);
                }

                continue;
            }

            // If the slug source is a custom field (e.g. 'name' instead of 'title'),
            // mirror its value into $page->setTitle() so Gedmo Sluggable produces a
            // valid entity-column slug instead of "-1".
            if (null !== $this->slugSource
                && $name === $this->slugSource
                && !in_array($name, self::TRANSLATABLE_ENTITY_FIELDS, true)
                && method_exists($data, 'setTitle')
            ) {
                $mirrored = $this->normalizeValue($form->getData());
                if (null !== $mirrored && '' !== $mirrored) {
                    $data->setTitle((string) $mirrored);
                }
                // Fall through: still store the custom field in jsonContent below.
            }

            // Special handling for Aropixel Image/File types when they don't use a data_class
            // (common in JSON storage scenarios)
            if (null === $form->getConfig()->getDataClass()) {
                if ('aropixel_admin_image' === $blockPrefix || 'aropixel_admin_file' === $blockPrefix) {
                    $values[$name] = $this->extractFormData($form);
                    continue;
                }

                if ('aropixel_admin_gallery' === $blockPrefix || 'aropixel_admin_gallery_image' === $blockPrefix) {
                    $values[$name] = [];
                    foreach ($form as $child) {
                        $values[$name][] = $this->extractFormData($child);
                    }
                    continue;
                }
            }

            $values[$name] = $this->normalizeValue($form->getData());
        }

        // Store the assembled JSON payload as a locale-specific translation so that
        // each locale has its own jsonContent in aropixel_page_translation.
        $this->upsertTranslation($data, 'jsonContent', json_encode($values) ?: null);

        // Ensure $page->title is non-null for Gedmo Sluggable (which reads the entity
        // column, not the translation table).  When the slug source field is a
        // TranslatableType (handled by TranslatableMapper before us), our loop never
        // calls setTitle().  We recover the value from the already-updated collection.
        if (null !== $this->slugSource && method_exists($data, 'setTitle')) {
            $sourceField = in_array($this->slugSource, self::TRANSLATABLE_ENTITY_FIELDS, true)
                ? $this->slugSource
                : null; // custom non-entity slugSource: setTitle() was already called above
            if (null !== $sourceField) {
                foreach ($data->getTranslations() as $t) {
                    if ($t->getLocale() === $this->currentLocale && $t->getField() === $sourceField) {
                        $data->setTitle((string) $t->getContent());
                        break;
                    }
                }
            }
        }
    }

    /**
     * Creates or updates the PersonalTranslation row for ($currentLocale, $field)
     * directly in the entity's translations collection.
     *
     * Passing null or '' removes the translation (same behaviour as TranslatableMapper).
     */
    private function upsertTranslation(mixed $entity, string $field, ?string $value): void
    {
        $translations = $entity->getTranslations();

        $existing = null;
        foreach ($translations as $t) {
            if ($t->getLocale() === $this->currentLocale && $t->getField() === $field) {
                $existing = $t;
                break;
            }
        }

        if (null === $value || '' === $value) {
            if ($existing) {
                $translations->removeElement($existing);
                if (method_exists($existing, 'setObject')) {
                    $existing->setObject(null);
                }
            }

            return;
        }

        if ($existing) {
            $existing->setContent($value);
        } else {
            /** @var object $newTranslation */
            $newTranslation = new $this->translationClass($this->currentLocale, $field, $value);
            if (method_exists($newTranslation, 'setObject')) {
                $newTranslation->setObject($entity);
            }
            $translations->add($newTranslation);
        }
    }

    /**
     * Converts a stored JSON scalar back to the model type expected by the form field.
     * Currently handles date/time/datetime fields (string → \DateTime).
     */
    private function denormalizeValue(mixed $value, FormInterface $form): mixed
    {
        if (!is_string($value) || '' === $value) {
            return $value;
        }

        $blockPrefix = $form->getConfig()->getType()->getInnerType()->getBlockPrefix();

        if (in_array($blockPrefix, ['date', 'time', 'datetime'], true)) {
            try {
                return new \DateTime($value);
            } catch (\Exception) {
                return null;
            }
        }

        return $value;
    }

    /**
     * Converts a model value to a JSON-serializable scalar.
     * Currently handles \DateTimeInterface (→ Y-m-d string).
     */
    private function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return $value;
    }

    private function isTranslatableField(FormInterface $form): bool
    {
        return 'aropixel_admin_translatable' === $form->getConfig()->getType()->getInnerType()->getBlockPrefix();
    }

    /**
     * Extracts all child field data from a composite form.
     */
    private function extractFormData(FormInterface $form): array
    {
        $data = [];
        foreach ($form as $childName => $childForm) {
            $data[$childName] = $childForm->getData();
        }

        return $data;
    }
}
