<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/project')]
class ProjectController extends AbstractController
{
    #[Route('/', name: 'app_project_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // get current user
        $user = $this->getUser();

        // get all projects for the current user
        $projects = $projectRepository->findBy(['owner' => $user]);

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/new', name: 'app_project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProjectRepository $projectRepository): Response
    {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // get current user
        $user = $this->getUser();

        $project = new Project();
        $project->setOwner($user);
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $projectRepository->save($project, true);

            return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('project/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_project_show', methods: ['GET'])]
    public function show(Project $project): Response
    {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // get current user
        $user = $this->getUser();

        // deny access if the user is not the owner of the project
        if ($project->getOwner() !== $user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, ProjectRepository $projectRepository): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $projectRepository->save($project, true);

            return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('project/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_project_delete', methods: ['POST'])]
    public function delete(Request $request, Project $project, ProjectRepository $projectRepository): Response
    {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // get current user
        $user = $this->getUser();

        // deny access if the user is not the owner of the project
        if ($project->getOwner() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->request->get('_token'))) {
            $projectRepository->remove($project, true);
        }

        return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
    }
}
