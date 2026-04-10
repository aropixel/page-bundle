<?php

namespace Aropixel\PageBundle\Form\DataMapper;

use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormInterface;

class JsonPageDataMapper implements DataMapperInterface
{
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
            if (isset($values[$name])) {
                $form->setData($values[$name]);
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

            $values[$name] = $form->getData();
        }

        $data->setJsonContent(json_encode($values));
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
