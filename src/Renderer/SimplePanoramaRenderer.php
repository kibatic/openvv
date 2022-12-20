<?php

namespace App\Renderer;

use App\Entity\Media;
use App\Entity\Project;
use App\Repository\MediaRepository;
use Symfony\Component\Routing\RouterInterface;

class SimplePanoramaRenderer extends AbstractRenderer
{
    public function __construct(
        private readonly MediaRepository $mediaRepository,
        private readonly RouterInterface $router,
    ) {
        parent::__construct($router);
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

    public function getPanorama(Project $project, bool $isPublic = true): string
    {
        $media = $this->getFirstMedia($project);
        return $this->getMediaUrl($media, $isPublic);
    }
    public function getInitialPosition(Project $project): array
    {
        $media = $this->getFirstMedia($project);
        return [
            'yaw' => $media->getInitialLongitude(),
            'pitch' => $media->getInitialLatitude(),
        ];
    }
}
