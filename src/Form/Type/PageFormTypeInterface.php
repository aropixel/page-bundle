<?php

namespace Aropixel\PageBundle\Form\Type;

use Symfony\Component\Form\FormTypeInterface;

interface PageFormTypeInterface extends FormTypeInterface
{
    public function getType(): string;
}
