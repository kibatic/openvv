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
            return $this->redirectToRoute('app_media_show', ['id' => $link->getSourceMedia()->getId()]);
        }

        return $this->render('link/edit.html.twig', [
            'link' => $link,
            'form' => $form->createView(),
        ]);
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
        }

        return $this->redirectToRoute('app_media_show', ['id' => $link->getSourceMedia()->getId()], Response::HTTP_SEE_OTHER);
    }
}
