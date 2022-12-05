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

    protected function getFirstMedia(Project $project): ?Media
    {
        return $this->mediaRepository->createQueryBuilder('m')
            ->where('m.project = :project')
            ->setParameter('project', $project)
            ->orderBy('m.orderInProject', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;
    }

    public function getPanorama(Project $project): string
    {
        $media = $this->getFirstMedia($project);
        return $this->router->generate('app_media_download_public', [
            'shareUid' => $project->getShareUid(),
            'id' => $media->getId()
        ]);
    }
    public function getInitialPosition(Project $project): array
    {
        $media = $this->getFirstMedia($project);
        return [
            'longitude' => $media->getInitialLongitude(),
            'latitude' => $media->getInitialLatitude(),
        ];
    }
}
