<?php

namespace App\Service;

use App\Entity\Media;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Process\Process;

class MediaManager
{
    public function __construct(
        private FilesystemOperator $originalMediaStorage,
        private FilesystemOperator $thumbnailStorage,
        private FilesystemOperator $mediaStorage
    ) {
    }

    public function applyFiltersToMedia(Media $media, bool $force = false): void
    {
        // check if the media already exists
        $imagePath = $media->vichDirectoryName().'/'.$media->getMediaName();
        if (
            $this->mediaStorage->fileExists($imagePath) &&
            $force === false
        ) {
            return;
        }

        // copy the image in a local file
        $content = $this->originalMediaStorage->read($imagePath);
        $originalName = sys_get_temp_dir().'/original_'.$media->getMediaName();
        file_put_contents($originalName, $content);

        // apply the filters
        if ($media->isLuminosityFilterApplied()) {
            $targetName = sys_get_temp_dir().'/target_'.$media->getMediaName();
            $this->applyLuminosityFilter($originalName, $targetName);
            $this->mediaStorage->write($imagePath, file_get_contents($targetName));
            return;
        }
        // no filter, only copy the original file to media
        $this->mediaStorage->write($imagePath, file_get_contents($originalName));
    }
    public function generateThumbnail(Media $media, bool $force = false): void
    {
        // check if the media already exists
        $imagePath = $media->vichDirectoryName().'/'.$media->getMediaName();
        if (
            $this->thumbnailStorage->fileExists($imagePath) &&
            $force === false
        ) {
            return;
        }
        // copy the image in a local file
        $content = $this->mediaStorage->read($imagePath);
        $mediaName = sys_get_temp_dir().'/'.$media->getMediaName();
        file_put_contents($mediaName, $content);

        // apply the filters
        $targetName = sys_get_temp_dir().'/thumbnail_'.$media->getMediaName();
        $this->applyResizeFilterForThumbnail($mediaName, $targetName);

        // store thumbnail
        $this->thumbnailStorage->write($imagePath, file_get_contents($targetName));

        // remove temporary files
        unlink($mediaName);
        unlink($targetName);
    }

    protected function applyLuminosityFilter(string $srcFile, string $targetFile): void
    {
        $process = new Process(['convert', $srcFile, '-evaluate', 'Log', '5', '-kuwahara', '1', $targetFile]);
        $process->mustRun();
    }

    protected function applyResizeFilterForThumbnail(string $srcFile, string $targetFile): void
    {
        $process = new Process(['convert', $srcFile, '-thumbnail', '200x200', $targetFile]);
        $process->mustRun();
    }
}
