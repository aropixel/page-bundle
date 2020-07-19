<?php

declare(strict_types=1);

namespace Aropixel\PageBundle\Twig;

use Aropixel\PageBundle\Entity\FieldInterface;
use Aropixel\PageBundle\Entity\PageInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;


class FieldExtension extends AbstractExtension
{


    public function getFilters()
    {
        return [
            new TwigFilter('field', [$this, 'getField'])
        ];
    }


    public function getField(PageInterface $page, string $code)
    {
        $pageField = $page->getField($code);
        if ($pageField instanceof FieldInterface) {

            //
            $propertyAccessor = PropertyAccess::createPropertyAccessor();

            try {
                $value = $propertyAccessor->getValue($pageField, $code);
            }
            catch (\Exception $e) {
                $value = $pageField->getValue();
            }

            //
            return $value;
        }

        return $pageField;
    }

}
