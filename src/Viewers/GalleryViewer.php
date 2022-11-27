<?php

namespace App\Viewers;

use App\Entity\Project;
use App\Repository\MediaRepository;
use Symfony\Component\Routing\RouterInterface;

class GalleryViewer
{
    public function __construct(
        private readonly MediaRepository $mediaRepository,
        private readonly RouterInterface $router,
    ) {
    }

    public function getItems(Project $project): array
    {
        $mediaList = $this->mediaRepository->createQueryBuilder('m')
            ->where('m.project = :project')
            ->setParameter('project', $project)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        $items = [];
        foreach ($mediaList as $media) {
            $items[] = [
                'id' => 'pano-'.$media->getId(),
                'name' => $media->getName(),
                'panorama' => $this->router->generate('app_media_download_public', [
                    'shareUid' => $project->getShareUid(),
                    'id' => $media->getId()
                ]),
                'thumbnail' => $this->router->generate('app_media_thumbnail_public', [
                    'shareUid' => $project->getShareUid(),
                    'id' => $media->getId()
                ]),
                'options' => [
                    'caption' => $project->getName()." : ".$media->getName()
                ]
            ];
        }
        return $items;
    }
}
