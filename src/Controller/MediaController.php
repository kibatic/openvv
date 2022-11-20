<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Project;
use App\Form\MediaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Handler\DownloadHandler;

class MediaController extends AbstractController
{
    #[Route('/project/{project}/media/new', name: 'app_media_new', methods: ['GET', 'POST'])]
    public function new(
        Project $project,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        // deny if the user is not the owner of the project
        if ($user !== $project->getOwner()) {
            throw $this->createAccessDeniedException('You are not allowed to access this page.');
        }

        // create form for a new Media
        $media = new Media();
        $media->setProject($project);
        $form = $this->createForm(MediaType::class, $media);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($media);
            $entityManager->flush();
            // add flash message
            $this->addFlash('success', 'Media created successfully.');
            // redirect to the project show page
            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        return $this->render('media/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/media/{id}', name: 'app_media_show', methods: ['GET'])]
    public function show(
        Media $media
    ): Response {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        // deny if the user is not the owner of the project
        if ($user !== $media->getProject()->getOwner()) {
            throw $this->createAccessDeniedException('You are not allowed to access this page.');
        }

        return $this->render('media/show.html.twig', [
            'media' => $media
        ]);
    }

    #[Route('/media/{id}/download', name: 'app_media_download', methods: ['GET'])]
    public function download(
        Media $media,
        DownloadHandler $downloadHandler
    ): Response {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        // deny if the user is not the owner of the project
        if ($user !== $media->getProject()->getOwner()) {
            throw $this->createAccessDeniedException('You are not allowed to access this page.');
        }

        return $downloadHandler->downloadObject($media, 'mediaFile', null, null, false);
    }
}
