<?php

namespace App\Controller;

use App\Entity\Link;
use App\Entity\Media;
use App\Entity\Project;
use App\Entity\User;
use App\Form\LinkType;
use App\Form\MediaEditType;
use App\Form\MediaType;
use App\Repository\LinkRepository;
use App\Repository\MediaRepository;
use App\Service\MediaManager;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MediaController extends AbstractController
{
    #[Route('/project/{project}/media/new', name: 'app_media_new', methods: ['GET', 'POST'])]
    public function new(
        Project $project,
        Request $request,
        EntityManagerInterface $entityManager,
        MediaManager $mediaManager
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
        $media->setIsLuminosityFilterApplied(true);
        $form = $this->createForm(MediaType::class, $media);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($media);
            $entityManager->flush();
            // here an event is dispatched by vich_uploader. This event generated the
            // filtered versions of the media and the thumbnail
            $this->addFlash('success', 'Media created successfully.');
            return $this->redirectToRoute('app_media_edit', ['id' => $media->getId()]);
        }

        return $this->render('media/new.html.twig', [
            'form' => $form->createView(),
            'project' => $project,
        ]);
    }

    #[Route('/media/{id}', name: 'app_media_show', methods: ['GET', 'POST'])]
    public function show(
        Media $media,
        Request $request,
        EntityManagerInterface $entityManager,
        LinkRepository $linkRepository,
        MediaRepository $mediaRepository,
    ): Response {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        // deny if the user is not the owner of the project
        if ($user !== $media->getProject()->getOwner()) {
            throw $this->createAccessDeniedException('You are not allowed to access this page.');
        }

        // check if we need the link creation form
        $targetMediaList = $mediaRepository->createQueryBuilder('m')
            ->where('m.project = :project')
            ->andWhere('m.id != :currentMedia')
            ->orderBy('m.orderInProject', 'ASC')
            ->setParameter('currentMedia', $media->getId())
            ->setParameter('project',$media->getProject())
            ->getQuery()
            ->getResult()
        ;

        // create a form for a new Link
        $link = new Link();
        $link->setSourceMedia($media);
        $form = $this->createForm(LinkType::class, $link, ['project' => $media->getProject(), 'media' => $media]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($link);
            $entityManager->flush();
            $this->addFlash('success', 'Link created successfully.');
            return $this->redirectToRoute('app_link_edit', ['id' => $link->getId()]);
        }

        $qb = $linkRepository->createQueryBuilder('l')
            ->where('l.sourceMedia = :media')
            ->setParameter('media', $media)
            ->orderBy('l.createdAt', 'DESC');
        $fromMeLinks = $qb->getQuery()->getResult();

        $backLinks = [];
        foreach ($fromMeLinks as $link) {
            if ($backlink = $linkRepository->getBackLink($link)) {
                $backLinks['link-'.$link->getId()] = $backlink->getId();
            }
        }

        return $this->render('media/show.html.twig', [
            'media' => $media,
            'form' => $form->createView(),
            'fromMeLinks' => $fromMeLinks,
            'targetMediaList' => $targetMediaList,
            'backLinks' => $backLinks,
        ]);
    }

    #[Route('/media/{id}/edit', name: 'app_media_edit', methods: ['GET', 'POST'])]
    public function edit(
        Media $media,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        // deny if the user is not the owner of the project
        if ($user !== $media->getProject()->getOwner()) {
            throw $this->createAccessDeniedException('You are not allowed to access this page.');
        }

        $form = $this->createForm(MediaEditType::class, $media);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($media);
            $entityManager->flush();
            // here an event is dispatched by vich_uploader. This event generated the
            // filtered versions of the media and the thumbnail
            // this event is dispatched only if the media is modified.

            // add flash message
            $this->addFlash('success', 'Media updated successfully.');
            // redirect to the project show page
            return $this->redirectToRoute('app_media_show', ['id' => $media->getId()]);
        }

        return $this->render('media/edit.html.twig', [
            'media' => $media,
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        '/media/{id}/toggle-filters',
        name: 'app_media_toggle-filters',
        methods: ['POST']
    )]
    public function toggleFilters(
        Media $media,
        MediaManager $mediaManager,
        MediaRepository $mediaRepository,
        Request $request
    ): Response {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        // deny if the user is not the owner of the project
        if ($user !== $media->getProject()->getOwner()) {
            throw $this->createAccessDeniedException('You are not allowed to access this page.');
        }

        if (!$this->isCsrfTokenValid('toggleFilters'.$media->getId(), $request->request->get('_token'))) {
            $this->addFlash('warning', 'Invalid CSRF token. Filters not applied.');
            return $this->redirectToRoute('app_media_show', ['id' => $media->getId()]);
        }

        $media->setIsLuminosityFilterApplied(!$media->isLuminosityFilterApplied());
        $mediaRepository->save($media, true);
        $mediaManager->applyFiltersToMedia($media, true);
        $mediaManager->generateThumbnail($media, true);

        // add flash message
        $this->addFlash('success', 'Panorama modified.');
        // redirect to the project show page
        return $this->redirectToRoute('app_media_show', ['id' => $media->getId()]);
    }

    #[Route('/media/{id}/delete', name: 'app_media_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Media $media,
        MediaRepository $mediaRepository
    ): Response {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // get current user
        $user = $this->getUser();

        // deny access if the user is not the owner of the project
        if ($media->getProject()->getOwner() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$media->getId(), $request->request->get('_token'))) {
            $mediaRepository->remove($media, true);
        }

        return $this->redirectToRoute('app_project_show', ['id' => $media->getProject()->getId()], Response::HTTP_SEE_OTHER);
    }


    #[Route('/media/{id}/download', name: 'app_media_download', methods: ['GET'])]
    public function download(
        Media $media,
        FilesystemOperator $mediaStorage
    ): Response {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        // deny if the user is not the owner of the project
        if ($user !== $media->getProject()->getOwner()) {
            throw $this->createAccessDeniedException('You are not allowed to access this page.');
        }

        return $this->getImageResponse($mediaStorage, $media);
    }

    #[Route('/share/{shareUid}/media/{id}/download-public', name: 'app_media_download_public', methods: ['GET'])]
    #[ParamConverter('project', options: ['mapping' => ['shareUid' => 'shareUid']])]
    #[ParamConverter('media', options: ['mapping' => ['id' => 'id']])]
    public function downloadPublic(
        Project $project,
        Media $media,
        FilesystemOperator $mediaStorage
    ): Response {
        if (!$project->isShareActive()) {
            throw $this->createNotFoundException('Share is not active.');
        }
        if ($media->getProject() !== $project) {
            throw $this->createNotFoundException('Media does not belong to this project.');
        }

        return $this->getImageResponse($mediaStorage, $media);
    }
    #[Route('/media/{id}/thumbnail', name: 'app_media_thumbnail', methods: ['GET'])]
    public function thumbnail(
        Media $media,
        FilesystemOperator $thumbnailStorage,
        MediaManager $mediaManager
    ): Response {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        // deny if the user is not the owner of the project
        if ($user !== $media->getProject()->getOwner()) {
            throw $this->createAccessDeniedException('You are not allowed to access this page.');
        }
        $mediaManager->generateThumbnail($media);
        return $this->getImageResponse($thumbnailStorage, $media);
    }

    #[Route('/vich/{userId}/{projectId}/{filename}', name: 'app_media_vich', methods: ['GET'])]
    #[ParamConverter('user', options: ['mapping' => ['userId' => 'id']])]
    #[ParamConverter('project', options: ['mapping' => ['projectId' => 'id']])]
    public function vich(
        User $user,
        Project $project,
        string $filename,
        FilesystemOperator $thumbnailStorage,
        MediaManager $mediaManager
    ): Response {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($user !== $this->getUser()) {
            throw $this->createAccessDeniedException('You are not allowed to access this page.');
        }
        // deny if the user is not the owner of the project
        if ($user !== $project->getOwner()) {
            throw $this->createAccessDeniedException('You are not allowed to access this page.');
        }
        $path = $user->getId() . '/' . $project->getId() . '/' . $filename;
        $imageContent = $thumbnailStorage->read($path);
        $contentType = $thumbnailStorage->mimeType($path);
        return new Response($imageContent, 200, ['Content-Type' => $contentType]);
    }

    #[Route('/share/{shareUid}/media/{id}/thumbnail', name: 'app_media_thumbnail_public', methods: ['GET'])]
    #[ParamConverter('project', options: ['mapping' => ['shareUid' => 'shareUid']])]
    #[ParamConverter('media', options: ['mapping' => ['id' => 'id']])]
    public function thumbnailPublic(
        Project $project,
        Media $media,
        FilesystemOperator $thumbnailStorage
    ): Response {
        if (!$project->isShareActive()) {
            throw $this->createNotFoundException('Share is not active.');
        }
        if ($media->getProject() !== $project) {
            throw $this->createNotFoundException('Media does not belong to this project.');
        }
        return $this->getImageResponse($thumbnailStorage, $media);
    }

    private function getImageResponse(FilesystemOperator $storage, Media $media): Response
    {
        $path = $media->vichDirectoryName().'/'.$media->getMediaName();
        $imageContent = $storage->read($path);
        $contentType = $storage->mimeType($path);
        return new Response($imageContent, 200, ['Content-Type' => $contentType]);
    }
}
