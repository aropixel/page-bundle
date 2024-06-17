<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 18/07/2020 à 00:00
 */

namespace Aropixel\PageBundle\Factory;


use Aropixel\PageBundle\Entity\FieldInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FieldFactory
{

    /**
     * FieldFactory constructor.
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(private readonly ParameterBagInterface $parameterBag)
    {
    }


    public function createField()
    {
        $entities = $this->parameterBag->get('aropixel_page.entities');
        $fieldEntityClass = $entities[FieldInterface::class];
        return new $fieldEntityClass();
    }

}
