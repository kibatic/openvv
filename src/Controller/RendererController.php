<?php

namespace App\Controller;

use App\Entity\Project;
use App\Enum\ProjectRendererEnum;
use App\Renderer\GalleryRenderer;
use App\Renderer\SimplePanoramaRenderer;
use App\Renderer\VirtualVisitRenderer;
use App\Repository\MediaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RendererController extends AbstractController
{
    #[Route('/view/{shareUid}', name: 'app_renderer_view', methods: ['GET'])]
    public function view(
        Project $project,
        MediaRepository $mediaRepository,
        GalleryRenderer $galleryRenderer,
        VirtualVisitRenderer $virtualVisitRenderer,
        SimplePanoramaRenderer $simplePanoramaRenderer,
        Request $request
    ): Response {
        switch ($project->getRenderer()) {
            case ProjectRendererEnum::GALLERY:
                $items = $galleryRenderer->getItems($project);
                return $this->render('renderer/gallery.html.twig', [
                    'items' => $items,
                    'project' => $project,
                ]);
            case ProjectRendererEnum::VIRTUAL_VISIT:
                $nodes = $virtualVisitRenderer->getNodes($project);
                return $this->render('renderer/virtual-visit.html.twig', [
                    'nodes' => $nodes,
                    'linkRotations' => $virtualVisitRenderer->getLinkRotations($project),
                    'mediaRotations' => $virtualVisitRenderer->getMediaRotations($project),
                    'project' => $project,
                ]);
            case ProjectRendererEnum::SIMPLE_PANORAMA:
                return $this->render('renderer/simple-panorama.html.twig', [
                    'project' => $project,
                    'initialPosition' => $simplePanoramaRenderer->getInitialPosition($project),
                    'panorama' => $simplePanoramaRenderer->getPanorama($project),
                ]);
            default:
                throw new \Exception('Renderer not found');
        }
    }
}
