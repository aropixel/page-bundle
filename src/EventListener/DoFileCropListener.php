<?php

namespace Aropixel\PageBundle\EventListener;

use Aropixel\AdminBundle\Domain\Media\Image\Crop\CropApplierInterface;
use Aropixel\AdminBundle\Entity\AttachedImageInterface;
use Aropixel\PageBundle\Entity\FieldInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;


#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
class DoFileCropListener
{
    /**
     */
    public function __construct(private readonly CropApplierInterface $cropper)
    {
    }

    public function postUpdate(PostUpdateEventArgs $args)
    {
        $this->doCrop($args);
    }

    public function postPersist(PostPersistEventArgs $args)
    {
        $this->doCrop($args);
    }

    private function doCrop(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        //
        if ($entity instanceof FieldInterface) {

            /** @var AttachedImageInterface $entity */
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
