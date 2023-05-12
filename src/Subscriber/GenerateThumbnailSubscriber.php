<?php

namespace App\Subscriber;

use App\Entity\Media;
use App\Service\MediaManager;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Vich\UploaderBundle\Event\Events;

class GenerateThumbnailSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MediaManager $mediaManager
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::POST_UPLOAD => 'onPostUpload',
        ];
    }

    public function onPostUpload($event)
    {
        /** @var Media $media */
        $media = $event->getObject();
        $this->mediaManager->applyFiltersToMedia($media, true);
        $this->mediaManager->generateThumbnail($media, true);
    }
}
