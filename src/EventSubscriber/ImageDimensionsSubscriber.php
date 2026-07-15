<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\AboutMe;
use App\Entity\Article;
use App\Entity\ArticleContent;
use App\Entity\Project;
use App\Entity\ProjectImage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;

class ImageDimensionsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_UPLOAD => 'onPostUpload',
        ];
    }

    public function onPostUpload(Event $event): void
    {
        $entity  = $event->getObject();
        $mapping = $event->getMapping();

        $uploadDir = $mapping->getUploadDestination();
        $subDir    = $mapping->getUploadDir($entity);
        $fileName  = $mapping->getFileName($entity);

        if (null === $fileName) {
            return;
        }

        $path = $subDir
            ? $uploadDir . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . $fileName
            : $uploadDir . DIRECTORY_SEPARATOR . $fileName;

        if (!file_exists($path)) {
            return;
        }

        $size = getimagesize($path);
        if (false === $size) {
            return;
        }

        [$width, $height] = $size;

        match (true) {
            $entity instanceof Project => $entity
                                                ->setMainImageWidth($width)
                                                ->setMainImageHeight($height),
            $entity instanceof Article => $entity
                                                ->setMainImageWidth($width)
                                                ->setMainImageHeight($height),
            $entity instanceof ProjectImage => $entity
                                                ->setImageWidth($width)
                                                ->setImageHeight($height),
            $entity instanceof ArticleContent => $entity
                                                ->setImageWidth($width)
                                                ->setImageHeight($height),
            $entity instanceof AboutMe => $entity
                                                ->setProfilePictureWidth($width)
                                                ->setProfilePictureHeight($height),
            default => null,
        };
    }
}
