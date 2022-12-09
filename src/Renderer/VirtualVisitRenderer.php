<?php

namespace App\Renderer;

use App\Entity\Media;
use App\Entity\Project;
use App\Repository\LinkRepository;
use App\Repository\MediaRepository;
use Symfony\Component\Routing\RouterInterface;

class VirtualVisitRenderer
{
    public function __construct(
        private readonly MediaRepository $mediaRepository,
        private readonly LinkRepository $linkRepository,
        private readonly RouterInterface $router,
    ) {
    }

    public function getNodes(Project $project): array
    {
        $mediaList = $this->mediaRepository->findByProject($project);
        foreach ($mediaList as $media) {
            $nodes[] = [
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
                'links' => $this->getProjectLinks($media),
                'caption' => $project->getName()." : ".$media->getName()
            ];
        }
        return $nodes;
    }

    protected function getProjectLinks(Media $media): array
    {
        $linksBySource = $this->linkRepository->createQueryBuilder('l')
            ->where('l.sourceMedia = :media')
            ->andWhere('l.sourceLongitude IS NOT NULL')
            ->andWhere('l.sourceLatitude IS NOT NULL')
            ->andWhere('l.targetLatitude IS NOT NULL')
            ->andWhere('l.targetLongitude IS NOT NULL')
            ->setParameter('media', $media)
            ->getQuery()->getResult()
        ;
        // merge linksByTarget and linksBySource and deduplicate
        $links = [];
        foreach ($linksBySource as $link) {
            $links[$link->getId()] = $link;
        }

        $projectLinks = [];
        foreach ($links as $link) {
            $projectLinks[] = [
                'nodeId' => 'pano-'.$link->getTargetMedia()->getId(),
                'latitude' => $link->getSourceLatitude(),
                'longitude' => $link->getSourceLongitude()
            ];
        }
        return $projectLinks;
    }

    public function getMediaRotations(Project $project): array
    {
        $mediaList = $this->mediaRepository->findByProject($project);
        $rotations = [];
        foreach ($mediaList as $media) {
            $rotations[$media->getNodeId()] = [
                'longitude' => $media->getInitialLongitude(),
                'latitude' => $media->getInitialLatitude(),
            ];
        }
        return $rotations;
    }

    public function getLinkRotations(Project $project): array
    {
        $links = $this->linkRepository->findByProject($project);

        $rotations = [];
        foreach ($links as $link) {
            if (!isset($rotations[$link->getSourceMedia()->getNodeId()])) {
                $rotations[$link->getSourceMedia()->getNodeId()] = [];
            }
            $rotations[$link->getSourceMedia()->getNodeId()][$link->getTargetMedia()->getNodeId()] = [
                'longitude' => $link->getTargetLongitude(),
                'latitude' => $link->getTargetLatitude(),
            ];
        }
        return $rotations;
    }
}
