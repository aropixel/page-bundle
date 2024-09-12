<?php


namespace Aropixel\PageBundle\Form\DataMapper;

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
    /** @var PropertyAccessorInterface|null  */
    private $propertyAccessor;


    public function __construct(private readonly EntityManagerInterface $em, private readonly FieldFactory $fieldFactory, PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * Give data values to form when form is loaded
     * @param Page|null $data
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        $empty = null === $viewData || [] === $viewData;

        if (!$empty && !\is_array($viewData) && !\is_object($viewData)) {
            throw new UnexpectedTypeException($viewData, 'object, array or empty');
        }

        $formValues = [];

        /** @var FormInterface $form */
        foreach ($forms as $form) {

            $propertyPath = $form->getPropertyPath();
            $config = $form->getConfig();

            if (!$empty && null !== $propertyPath) {

                // If current form field really map a page field entity, it's OK
                try {
                    $value = $this->propertyAccessor->getValue($viewData, $propertyPath);
                    $form->setData($value);
                }

                // Otherwise, an exception is sent
                catch (NoSuchPropertyException $e) {

                    /**
                     * Then, we iterate each field of the page to check if it map current form field
                     * @var Field $field
                     */
                    foreach ($viewData->getFields() as $field) {

                        $keys = explode('.', $field->getCode());
                        if (current($keys) == $propertyPath) {

                            // Shift the first element, to treate childs
                            $rootKey = array_shift($keys);
                            if ($field->getFormType() == 'ImageType' || $field->getFormType() == 'GalleryImageType') {
                                $fieldValue = $field;
                            }
                            else {
                                $fieldValue = $this->getFieldValue($field);
                            }

                            $value = $this->explodeValue($keys, $fieldValue);

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
     * @param FieldInterface $field
     * @return mixed
     */
    protected function getFieldValue(FieldInterface $field)
    {
        try {
            // try to get value from a "real" property
            $keys = explode('.', $field->getCode());
            $last = end($keys);
            $value = $this->propertyAccessor->getValue($field, $last);
        }
        catch (\Exception $e) {

            // if not, use the value property
            $value = $field->getValue();

        }

        return $value;
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
    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        if (null === $viewData) {
            return;
        }

        if (!\is_array($viewData) && !\is_object($viewData)) {
            throw new UnexpectedTypeException($viewData, 'object, array or empty');
        }

        $mappedFormFields = [];

        /**
         * Iterate each field of the page form
         * @var FormInterface $form
         */
        foreach ($forms as $form) {

            $propertyPath = $form->getPropertyPath();
            $propertyValue = $form->getData();

            $fullClassType = $form->getConfig()->getType()->getInnerType();
            $reflection = new \ReflectionClass($fullClassType);
            $type = $reflection->getShortName();

            if (!($propertyValue instanceof FieldInterface)) {

                if (null !== $propertyValue) {

                    // If the form field effectively map a page field, it's OK
                    try {
                        $this->propertyAccessor->setValue($viewData, $propertyPath, $propertyValue);
                        $mappedFormFields[] = (string)$propertyPath;
                    }

                        // Otherwise, an exception is sent
                    catch (NoSuchPropertyException $e) {

                        // Then we store the value in a Field
                        $this->mapToFieldData($viewData, $form, $propertyPath, $propertyValue, $mappedFormFields);

                    }

                }

            } else {

                /** @var FieldInterface $field */
                $field = $propertyValue;

                if (!(is_null($field->getValue()) && $this->isFieldImage($field))) {

                    $field->setCode($propertyPath);
                    $field->setFormType($type);

                    /** @var PageInterface $page */
                    $page = $viewData;

                    if (!$field->getPage()) {
                        $page->addField($field);
                    }
                    $mappedFormFields[] = (string)$propertyPath;
                }

            }
        }

        /** @var FieldInterface $field */
        foreach ($viewData->getFields() as $field) {
            if (!in_array($field->getCode(), $mappedFormFields)) {
                $this->em->remove($field);
                $this->em->flush();
            }
        }
    }


    private function isFieldImage(FieldInterface $field)
    {
        return ($field->getFormType() == 'ImageType' || $field->getFormType() == 'GalleryType');
    }


    private function mapToFieldData($data, $form, $propertyPath, $propertyValue, &$mappedFormFields)
    {
        if (is_array($propertyValue)) {

            foreach ($propertyValue as $childPropertyPath => $childPropertyValue) {
                $this->mapToFieldData($data, $form->get($childPropertyPath), $propertyPath.'.'.$childPropertyPath, $childPropertyValue, $mappedFormFields);
            }

        }
        else {

            $fullClassType = $form->getConfig()->getType()->getInnerType();
            $reflection = new \ReflectionClass($fullClassType);
            $type = $reflection->getShortName();

            if (!($propertyValue instanceof FieldInterface)) {

                /**
                 * Check if a page field already exists for this form field
                 * @var Field $field
                 */
                $found = false;
                foreach ($data->getFields() as $field) {

                    if ($field->getCode() == $propertyPath) {
                        $found = true;
                        break;
                    }

                }

                // If no field was found, we create one for this page
                if (!$found) {
                    $field = $this->fieldFactory->createField();
                    $field->setCode($propertyPath);
                    $field->setFormType($type);
                    $data->addField($field);
                }

                try {
                    $path = explode('.', (string) $propertyPath);
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

                if (!(is_null($field->getValue()) && $this->isFieldImage($field))) {

                    //
                    $field->setCode($propertyPath);
                    $field->setFormType($type);

                    /** @var PageInterface $page */
                    $page = $data;

                    if (!$field->getPage()) {
                        $page->addField($field);
                    }

                }
            }

            $mappedFormFields[] = (string) $propertyPath;

        }

    }

}
