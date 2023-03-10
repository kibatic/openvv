<?php

namespace App\Renderer;

use App\Entity\Project;
use App\Repository\MediaRepository;
use Symfony\Component\Routing\RouterInterface;

class GalleryRenderer extends AbstractRenderer
{
    public function __construct(
        private readonly MediaRepository $mediaRepository,
        private readonly RouterInterface $router,
    ) {
        parent::__construct($router);
    }

    public function getItems(Project $project, bool $isPublic = true): array
    {
        $mediaList = $this->mediaRepository->findByProject($project);

        $items = [];
        foreach ($mediaList as $media) {
            $items[] = [
                'id' => 'pano-'.$media->getId(),
                'name' => $media->getName(),
                'panorama' => $this->getMediaUrl($media, $isPublic),
                'thumbnail' => $this->getThumbnailUrl($media, $isPublic),
                'defaultYaw' => $media->getInitialYaw(),
                'defaultPitch' => $media->getInitialPitch(),
                'defaultLong' => $media->getInitialYaw(),
                'defaultLat' => $media->getInitialPitch(),
                'options' => [
                    'caption' => $project->getName()." : ".$media->getName(),
                    'defaultYaw' => $media->getInitialYaw(),
                    'defaultPitch' => $media->getInitialPitch(),
                    'longitude' => $media->getInitialYaw(),
                    'latitude' => $media->getInitialPitch(),
                ]
            ];
        }
        return $items;
    }
}
