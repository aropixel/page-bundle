<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 06/07/2020 à 23:15
 */

namespace Aropixel\PageBundle\Form\DataTransformer;

use Aropixel\PageBundle\Entity\Field;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;


class FieldToStringTransformer implements DataTransformerInterface
{

    /**
     * Transforms an object (Field) to a string.
     *
     * @param  Field|null $field
     * @return string
     */
    public function transform($field)
    {
        return $field->getValue();
    }


    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $issueNumber
     * @return Field|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($value)
    {
        // TODO: Implement reverseTransform() method.
    }

}
