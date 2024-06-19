<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 18/06/2020 à 16:25
 */

namespace Aropixel\PageBundle\Entity;


interface PageInterface
{

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function setType(string $type) : PageInterface;


    public function addField(FieldInterface $field);

}
