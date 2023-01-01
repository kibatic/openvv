<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ShareType;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class ShareController extends AbstractController
{
    #[Route('/project/{id}/share/new', name: 'app_share_new', methods: ['GET', 'POST'])]
    public function new(
        Project $project,
        Request $request,
        ProjectRepository $projectRepository,
    ): Response {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        // deny if the user is not the owner of the project
        if ($user !== $project->getOwner()) {
            throw $this->createAccessDeniedException('You are not allowed to access this page.');
        }
        $form = $this->createForm(ShareType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($project->getShareUid() === null) {
                $project->setShareUid(Uuid::v4());
            }
            $project->setShareStartedAt(new \DateTimeImmutable());
            $projectRepository->save($project, true);

            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()], Response::HTTP_SEE_OTHER);
        }


        return $this->render('share/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/project/{id}/share/delete', name: 'app_share_delete', methods: ['GET', 'POST'])]
    public function delete(
        Project $project,
        Request $request,
        ProjectRepository $projectRepository,
    ): Response {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        // deny if the user is not the owner of the project
        if ($user !== $project->getOwner()) {
            throw $this->createAccessDeniedException('You are not allowed to access this page.');
        }
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->request->get('_token'))) {
            $project->setShareUid(null);
            $project->setShareStartedAt(null);
            $project->setShareDurationInDays(null);
            $projectRepository->save($project, true);
        }
        return $this->redirectToRoute('app_project_show', ['id' => $project->getId()], Response::HTTP_SEE_OTHER);
    }
}
