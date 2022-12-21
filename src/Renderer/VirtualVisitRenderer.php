<?php

namespace App\Renderer;

use App\Entity\Media;
use App\Entity\Project;
use App\Repository\LinkRepository;
use App\Repository\MediaRepository;
use Symfony\Component\Routing\RouterInterface;

class VirtualVisitRenderer extends AbstractRenderer
{
    public function __construct(
        private readonly MediaRepository $mediaRepository,
        private readonly LinkRepository $linkRepository,
        private readonly RouterInterface $router,
    ) {
        parent::__construct($router);
    }

    public function getNodes(Project $project, bool $isPublic = true): array
    {
        $mediaList = $this->mediaRepository->findByProject($project);
        foreach ($mediaList as $media) {
            $nodes[] = [
                'id' => 'pano-'.$media->getId(),
                'name' => $media->getName(),
                'panorama' => $this->getMediaUrl($media, $isPublic),
                'thumbnail' => $this->getThumbnailUrl($media, $isPublic),
                'links' => $this->getProjectLinks($media),
                'caption' => $project->getName()." : ".$media->getName(),
                'defaultYaw' => $media->getInitialYaw(),
                'defaultPitch' => $media->getInitialPitch(),
            ];
        }
        return $nodes;
    }

    protected function getProjectLinks(Media $media): array
    {
        $linksBySource = $this->linkRepository->createQueryBuilder('l')
            ->where('l.sourceMedia = :media')
            ->andWhere('l.sourceYaw IS NOT NULL')
            ->andWhere('l.sourcePitch IS NOT NULL')
            ->andWhere('l.targetYaw IS NOT NULL')
            ->andWhere('l.targetPitch IS NOT NULL')
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
                'pitch' => $link->getSourcePitch(),
                'yaw' => $link->getSourceYaw(),
                'markerStyle' => [
                    'html' => null,
                    'image' => '/assets/pin-red.png'
                    //'html' => 'tutu', // an SVG provided by the plugin
//                    'size' => [ 'width' => 100, 'height'=> 100 ],
//                    'scale' => [0.5, 2],
//                    'anchor' => 'top center',
//                    'className' => 'psv-virtual-tour__marker',
//                    'style' => [
//                      'color' => 'rgba(255, 255, 128, 1)',
//                    ]
                ],
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
                'yaw' => $media->getInitialYaw(),
                'pitch' => $media->getInitialPitch(),
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
                'yaw' => $link->getTargetYaw(),
                'pitch' => $link->getTargetPitch(),
            ];
        }
        return $rotations;
    }
}
