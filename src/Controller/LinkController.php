<?php

namespace App\Controller;

use App\Entity\Link;
use App\Form\EditLinkType;
use App\Repository\LinkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinkController extends AbstractController
{
    #[Route('/link/{id}/edit', name: 'app_link_edit')]
    public function edit(
        Link $link,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        // create a form for a new Link
        $form = $this->createForm(EditLinkType::class, $link);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($link);
            $entityManager->flush();
            $this->addFlash('success', 'Link updated successfully.');
            return $this->redirectToRoute('app_media_show', ['id' => $link->getSourceMedia()->getId()]);
        }

        return $this->render('link/edit.html.twig', [
            'link' => $link,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/link/{id}/create-backlink', name: 'app_link_create_backlink', methods: ['POST'])]
    public function createBacklink(
        Request $request,
        Link $link,
        EntityManagerInterface $entityManager,
    ): Response {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // get current user
        $user = $this->getUser();

        // deny access if the user is not the owner of the project
        if ($link->getSourceMedia()->getProject()->getOwner() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('create_backlink'.$link->getId(), $request->request->get('_token'))) {
            $backlink = new Link();
            $backlink->setSourceMedia($link->getTargetMedia());
            $backlink->setTargetMedia($link->getSourceMedia());
            $entityManager->persist($backlink);
            $entityManager->flush();
            $this->addFlash('success', 'Back link created successfully.');
            return $this->redirectToRoute('app_link_edit', ['id' => $backlink->getId()]);
        }

        return $this->redirectToRoute('app_media_show', ['id' => $link->getSourceMedia()->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/link/{id}/delete', name: 'app_link_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Link $link,
        LinkRepository $mediaRepository
    ): Response {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // get current user
        $user = $this->getUser();

        // deny access if the user is not the owner of the project
        if ($link->getSourceMedia()->getProject()->getOwner() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$link->getId(), $request->request->get('_token'))) {
            $mediaRepository->remove($link, true);
            $this->addFlash('success', 'Link removed.');
        }

        return $this->redirectToRoute('app_media_show', ['id' => $link->getSourceMedia()->getId()], Response::HTTP_SEE_OTHER);
    }
}
