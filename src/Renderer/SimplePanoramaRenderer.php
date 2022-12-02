<?php

namespace App\Renderer;

use App\Entity\Media;
use App\Entity\Project;
use App\Repository\MediaRepository;
use Symfony\Component\Routing\RouterInterface;

class SimplePanoramaRenderer
{
    public function __construct(
        private readonly MediaRepository $mediaRepository,
        private readonly RouterInterface $router,
    ) {
    }

    public function getPanorama(Project $project): string
    {
        /** @var Media $media */
        $media = $this->mediaRepository->createQueryBuilder('m')
            ->where('m.project = :project')
            ->setParameter('project', $project)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;
        return $this->router->generate('app_media_download_public', [
            'shareUid' => $project->getShareUid(),
            'id' => $media->getId()
        ]);
    }
    public function getInitialPosition(Project $project): array
    {
        /** @var Media $media */
        $media = $this->mediaRepository->createQueryBuilder('m')
            ->where('m.project = :project')
            ->setParameter('project', $project)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;
        return [
            'longitude' => $media->getInitialLongitude(),
            'latitude' => $media->getInitialLatitude(),
        ];
    }
}
