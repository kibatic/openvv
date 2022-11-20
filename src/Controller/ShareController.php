<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Share;
use App\Form\ShareType;
use App\Repository\ProjectRepository;
use App\Repository\ShareRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShareController extends AbstractController
{
    #[Route('/project/{id}/share/new', name: 'app_share_new', methods: ['GET', 'POST'])]
    public function new(
        Project $project,
        Request $request,
        ShareRepository $shareRepository,
    ): Response {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        // deny if the user is not the owner of the project
        if ($user !== $project->getOwner()) {
            throw $this->createAccessDeniedException('You are not allowed to access this page.');
        }
        $share = new Share();
        $share->setProject($project);
        $form = $this->createForm(ShareType::class, $share);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $shareRepository->save($share, true);

            return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
        }


        return $this->render('share/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
