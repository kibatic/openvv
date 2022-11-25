<?php

namespace App\Service;

use App\Entity\Media;
use League\Flysystem\FilesystemOperator;

class MediaManager
{
    public function __construct(
        private FilesystemOperator $mediaStorage,
        private FilesystemOperator $thumbnailStorage
    ) {
    }

    public function generateThumbnail(Media $media, bool $force = false): void
    {
        $imagePath = $media->vichDirectoryName().'/'.$media->getMediaName();
        if ($this->thumbnailStorage->fileExists($imagePath) && $force === false) {
            return;
        }
        $content = $this->mediaStorage->read($imagePath);
        $tmpName = sys_get_temp_dir().'/'.$media->getMediaName();
        file_put_contents($tmpName, $content);
        $imagick = new \Imagick($tmpName);
        $imagick->thumbnailImage(200, 200, true, true);
        $this->thumbnailStorage->write($imagePath, $imagick->getImageBlob());
        unlink($tmpName);
    }
}
