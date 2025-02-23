<?php

namespace Aropixel\PageBundle\EventListener;

use Aropixel\AdminBundle\Domain\Media\Image\Crop\CropApplierInterface;
use Aropixel\AdminBundle\Entity\AttachedImageInterface;
use Aropixel\AdminBundle\Entity\Image;
use Aropixel\PageBundle\Entity\FieldInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;


#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
class DoFileCropListener
{
    /**
     */
    public function __construct(
        private readonly CropApplierInterface $cropper,
        private readonly FilesystemOperator $privateStorage,
        private readonly LoggerInterface $logger,
    ) {
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

        if ($entity instanceof FieldInterface) {

            $fileName = $entity->getFilename();
            if (is_null($fileName))    return;

            /** @var FieldInterface $entity */
            $crops = $entity->getCrops();
            if ($crops && is_array($crops)) {
                foreach ($crops as $crop) {

                    try {
                        $contents = $this->privateStorage->read(Image::UPLOAD_DIR . '/' . $fileName);
                        [$width, $height] = getimagesizefromstring($contents);
                        $dtoImage = new Image();
                        $dtoImage->setWidth($width);
                        $dtoImage->setHeight($height);
                        $dtoImage->setFilename($fileName);
                        $this->cropper->applyCrop($fileName, $crop['filter'], $crop['crop']);
                    } catch (FilesystemException) {
                        $this->logger->error(sprintf('Unable to get image size: %s', Image::UPLOAD_DIR . '/' . $fileName));
                    }
                }
            }

        }


    }

}
