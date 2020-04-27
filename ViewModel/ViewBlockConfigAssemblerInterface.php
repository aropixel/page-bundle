<?php


namespace Aropixel\PageBundle\ViewModel;


interface ViewBlockConfigAssemblerInterface
{

    public function create($block): ViewBlock;
    public function createAll($blocks): array;

}
