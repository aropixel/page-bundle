<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aropixel\PageBundle\Form\DataMapper;

use Aropixel\AdminBundle\Form\Type\Image\Gallery\GalleryType;
use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use Aropixel\PageBundle\Entity\Field;
use Aropixel\PageBundle\Entity\FieldInterface;
use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Entity\PageInterface;
use Aropixel\PageBundle\Factory\FieldFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Maps arrays/objects to/from forms using property paths.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class PageFieldDataMapper implements DataMapperInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var FieldFactory */
    private $fieldFactory;

    /** @var PropertyAccessorInterface|null  */
    private $propertyAccessor;


    public function __construct(EntityManagerInterface $em, FieldFactory $fieldFactory, PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->em = $em;
        $this->fieldFactory = $fieldFactory;
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * Give data values to form when form is loaded
     * @param Page|null $data
     */
    public function mapDataToForms($data, iterable $forms)
    {
        $empty = null === $data || [] === $data;

        if (!$empty && !\is_array($data) && !\is_object($data)) {
            throw new UnexpectedTypeException($data, 'object, array or empty');
        }

        $formValues = [];

        /** @var FormInterface $form */
        foreach ($forms as $form) {

            //
            $propertyPath = $form->getPropertyPath();
            $config = $form->getConfig();
            $innerType = $config->getType()->getInnerType();

            if (!$empty && null !== $propertyPath) {

                // If current form field really map a page field entity, it's OK
                try {
                    $value = $this->propertyAccessor->getValue($data, $propertyPath);
                    $form->setData($value);
                }

                // Otherwise, an exception is sent
                catch (NoSuchPropertyException $e) {

                    /**
                     * Then, we iterate each field of the page to check if it map current form field
                     * @var Field $field
                     */
                    foreach ($data->getFields() as $field) {

                        $keys = explode('.', $field->getCode());
                        if (current($keys) == $propertyPath) {

                            // Shift the first element, to treate childs
                            $rootKey = array_shift($keys);
                            $fieldValue = (get_class($innerType) == ImageType::class || get_class($innerType) == GalleryType::class) ? $field : $field->getValue();
                            $value = $this->explodeValue($keys, $fieldValue);

                            //
                            if (!array_key_exists($rootKey, $formValues)) {
                                $formValues[$rootKey] = $value;
                            }
                            else {
                                $formValues[$rootKey] = array_replace_recursive($formValues[$rootKey], $value);
                            }
                        }

                    }
                }


            } else {
                $form->setData($config->getData());
            }
        }

        /** @var FormInterface $form */
        foreach ($forms as $form) {

            //
            $propertyPath = $form->getPropertyPath();
            foreach ($formValues as $rootKey => $value) {

                if ($propertyPath == $rootKey) {

                    if (is_array($value)) {
                        ksort($value);
                    }
                    $form->setData($value);

                }

            }
        }

    }


    /**
     * @param $childKeys
     * @param FormInterface $form
     * @return mixed
     */
    protected function explodeValue($childKeys, $value)
    {
        // Get the first child key
        $currentChildKey = array_pop($childKeys);

        // If there's no more child, we're done
        if (is_null($currentChildKey)) {
            return $value;
        }

        $newValue = [$currentChildKey => $value];
        return $this->explodeValue($childKeys, $newValue);
    }



    /**
     * Give form values to data when form is submitted
     */
    public function mapFormsToData(iterable $forms, &$data)
    {
        if (null === $data) {
            return;
        }

        if (!\is_array($data) && !\is_object($data)) {
            throw new UnexpectedTypeException($data, 'object, array or empty');
        }

        $mappedFormFields = [];

        // Iterate each field of the page form
        foreach ($forms as $form) {

            $propertyPath = $form->getPropertyPath();
            $propertyValue = $form->getData();

            //
            if (!($propertyValue instanceof FieldInterface)) {

                // If the form field effectively map a page field, it's OK
                try {
                    $this->propertyAccessor->setValue($data, $propertyPath, $propertyValue);
                    $mappedFormFields[] = (string)$propertyPath;
                }

                    // Otherwise, an exception is sent
                catch (NoSuchPropertyException $e) {

                    $this->mapToFieldData($data, $propertyPath, $propertyValue, $mappedFormFields);

                }
            }

        }

        /** @var FieldInterface $field */
        foreach ($data->getFields() as $field) {
            if (!in_array($field->getCode(), $mappedFormFields)) {
                $this->em->remove($field);
                $this->em->flush();
            }
        }
    }

    private function mapToFieldData($data, $propertyPath, $propertyValue, &$mappedFormFields)
    {

        if (is_array($propertyValue)) {
            foreach ($propertyValue as $childPropertyPath => $childPropertyValue) {
                $this->mapToFieldData($data, $propertyPath.'.'.$childPropertyPath, $childPropertyValue, $mappedFormFields);
            }
        }
        else {

            if (!($propertyValue instanceof FieldInterface)) {

                /**
                 * Check if a page field already exists for this form field
                 * @var Field $field
                 */
                $found = false;
                foreach ($data->getFields() as $field) {

                    if ($field->getCode() == $propertyPath) {
                        $field->setValue($propertyValue);
                        $found = true;
                        break;
                    }

                }

                // If no field was found, we create one for this page
                if (!$found) {
                    $field = $this->fieldFactory->createField();
                    $field->setCode($propertyPath);
                    $data->addField($field);
                }

                //
                try {
                    $path = explode('.', $propertyPath);
                    $fieldPath = end($path);
                    $this->propertyAccessor->setValue($field, $fieldPath, $propertyValue);
                }
                catch (\Exception $e) {
                    $field->setValue($propertyValue);
                }

            }
            else {

                /** @var FieldInterface $field */
                $field = $propertyValue;
                $field->setCode($propertyPath);

                /** @var PageInterface $page */
                $page = $data;

                if (!$field->getId()) {
                    $page->addField($field);
                }

            }

            $mappedFormFields[] = (string) $propertyPath;

        }

    }


}
