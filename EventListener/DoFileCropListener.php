<?php

namespace Aropixel\PageBundle\EventListener;

use Aropixel\AdminBundle\Entity\CropInterface;
use Aropixel\AdminBundle\Entity\ImageInterface;
use Aropixel\AdminBundle\Image\Cropper;
use Aropixel\PageBundle\Entity\FieldInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;


class DoFileCropListener
{
    /** @var Cropper  */
    private $cropper;

    /**
     */
    public function __construct(Cropper $cropper)
    {
        $this->cropper = $cropper;
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->doCrop($args);
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->doCrop($args);
    }

    public function doCrop(LifecycleEventArgs $args)
    {

        //
        $entity = $args->getEntity();

        //
        if ($entity instanceof FieldInterface) {

            /** @var ImageInterface $entity */
            $fileName = $entity->getFilename();
            if (is_null($fileName))    return;


            /** @var FieldInterface $entity */
            $crops = $entity->getCrops();
            if ($crops && is_array($crops)) {
                foreach ($crops as $crop) {

                    //
                    $this->cropper->applyCrop($fileName, $crop['filter'], $crop['crop']);

                }
            }

        }


    }

}
