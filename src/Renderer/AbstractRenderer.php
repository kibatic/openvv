<?php

namespace App\Renderer;

use App\Entity\Media;
use App\Repository\MediaRepository;
use Symfony\Component\Routing\RouterInterface;

class AbstractRenderer
{
    public function __construct(
        private readonly RouterInterface $router,
    ) {
    }

    public function getMediaUrl(Media $media, bool $isPublic): string
    {
        if ($isPublic) {
            return $this->router->generate('app_media_download_public', [
                'shareUid' => $media->getProject()->getShareUid(),
                'id' => $media->getId()
            ]);
        }
        return $this->router->generate('app_media_download', [
            'id' => $media->getId()
        ]);
    }

    public function getThumbnailUrl(Media $media, bool $isPublic): string
    {
        if ($isPublic) {
            return $this->router->generate('app_media_thumbnail_public', [
                'shareUid' => $media->getProject()->getShareUid(),
                'id' => $media->getId()
            ]);
        }
        return $this->router->generate('app_media_thumbnail', [
            'id' => $media->getId()
        ]);
    }
}
