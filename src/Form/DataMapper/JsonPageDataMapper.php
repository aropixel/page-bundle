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
            $values[$name] = $form->getData();
        }

        $data->setJsonContent(json_encode($values));
    }
}
