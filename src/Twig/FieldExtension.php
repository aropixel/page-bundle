<?php

declare(strict_types=1);

namespace Aropixel\PageBundle\Twig;

use Aropixel\PageBundle\Entity\FieldInterface;
use Aropixel\PageBundle\Entity\PageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;


class FieldExtension extends AbstractExtension
{

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getFilters()
    {
        return [
            new TwigFilter('field', $this->getField(...)),
        ];
    }


    public function getField(PageInterface $page, string $code)
    {
        $pageField = $page->getField($code);

        if ($pageField instanceof FieldInterface) {

            $propertyAccessor = PropertyAccess::createPropertyAccessor();

            try {
                $value = $propertyAccessor->getValue($pageField, $code);
            }
            catch (\Exception) {
                $value = $pageField->getValue();
            }

            return $value;
        }

        if ($this->isCollection($pageField)) {

            return $this->getTranslatableCollectionField($pageField);

        } elseif (is_array($pageField)) {

            return $this->getTranslatableField($pageField, $code);
        }

        return $pageField;
    }

    private function isCollection($pageField): bool
    {
        if (is_array($pageField)) {

            foreach ($pageField as $field) {
                return is_array($field);
            }

        }

        return false;
    }

    private function getTranslatableField(array $pageField, string $code)
    {
        $currentLocale = $this->getCurrentLocale();
        $key = $code . ':' . $currentLocale;

        return $pageField[$key];
    }

    public function getTranslatableCollectionField($pageField): array
    {
        $fieldCollection = [];

        foreach ($pageField as $collectionItem) {

            $fields = [];
            foreach ($collectionItem as $k => $item) {

                if (is_array($item)) {
                    $currentLocale = $this->getCurrentLocale();
                    $key = $k . ':' . $currentLocale;
                    $fields[$k] = $item[$key];
                } else {
                    $fields[$k] = $item;
                }

            }

            $fieldCollection[] = $fields;
        }

        return $fieldCollection;
    }

    private function getCurrentLocale()
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if (!$currentRequest instanceof Request) {
            return null;
        }

        $currentLocale = $currentRequest->getLocale();
        if ($currentLocale !== '') {
            return $currentLocale;
        }

        return null;
    }

}
