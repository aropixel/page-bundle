<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 05/07/2020 à 18:39
 */

namespace Aropixel\PageBundle\Form\Type;

use Symfony\Component\Form\FormTypeInterface;

interface PageFormTypeInterface extends FormTypeInterface
{
    public function getType() : string;
}
