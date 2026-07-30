<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Kibatic\DatagridBundle\Grid\GridBuilder;
use Kibatic\DatagridBundle\Grid\Template;
use Kibatic\DatagridBundle\Grid\Theme;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Interface d'administration des utilisateurs, réservée au rôle ROLE_ADMIN
 * (doublé par l'access_control ^/admin de security.yaml) : liste des comptes,
 * détail, activation/désactivation et suppression.
 */
#[Route('/admin/user')]
#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController
{
    #[Route('/', name: 'app_admin_user_index', methods: ['GET'])]
    public function index(
        Request $request,
        UserRepository $userRepository,
        GridBuilder $gridBuilder,
    ): Response {
        $queryBuilder = $userRepository->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC')
        ;

        $grid = $gridBuilder
            ->initialize($queryBuilder, request: $request)
            ->setTheme(Theme::BOOTSTRAP5)
            ->addColumn(
                'Email',
                function (User $user) {
                    if (!$user->isEnabled()) {
                        return sprintf(
                            '%s<br/><span class="badge bg-danger">disabled</span>',
                            htmlspecialchars($user->getEmail(), ENT_QUOTES),
                        );
                    }
                    return htmlspecialchars($user->getEmail(), ENT_QUOTES);
                },
                Template::TEXT,
                ['escape' => false],
            )
            ->addColumn(
                'Role',
                fn(User $user) => in_array('ROLE_ADMIN', $user->getRoles(), true) ? 'admin' : 'user',
                Template::TEXT
            )
            ->addColumn(
                'Created at',
                fn(User $user) => $user->getCreatedAt()->format('Y-m-d'),
                Template::TEXT
            )
            ->addColumn(
                'Last login',
                fn(User $user) => $user->getLastLoginAt()?->format('Y-m-d H:i') ?? 'never',
                Template::TEXT
            )
            ->addColumn(
                'Actions',
                fn(User $user) => [
                    [
                        'name' => 'Show',
                        'url' => $this->generateUrl('app_admin_user_show', ['id' => $user->getId()]),
                    ],
                ],
                Template::ACTIONS
            )
            ->getGrid()
        ;

        return $this->render('admin/user/index.html.twig', [
            'grid' => $grid,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_show', methods: ['GET'])]
    public function show(User $user, ProjectRepository $projectRepository): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
            'projectCount' => count($projectRepository->findBy(['owner' => $user])),
        ]);
    }

    #[Route('/{id}/disable', name: 'app_admin_user_disable', methods: ['POST'])]
    public function disable(User $user, Request $request, UserRepository $userRepository): Response
    {
        $this->checkCsrfToken('admin_user_disable', $user, $request);

        // un administrateur ne peut pas désactiver son propre compte
        if ($user === $this->getUser()) {
            $this->addFlash('danger', 'You cannot disable your own account.');
            return $this->redirectToRoute('app_admin_user_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        $user->setEnabled(false);
        $userRepository->save($user, true);
        $this->addFlash('success', 'User disabled successfully.');

        return $this->redirectToRoute('app_admin_user_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/enable', name: 'app_admin_user_enable', methods: ['POST'])]
    public function enable(User $user, Request $request, UserRepository $userRepository): Response
    {
        $this->checkCsrfToken('admin_user_enable', $user, $request);

        $user->setEnabled(true);
        $userRepository->save($user, true);
        $this->addFlash('success', 'User enabled successfully.');

        return $this->redirectToRoute('app_admin_user_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/promote', name: 'app_admin_user_promote', methods: ['POST'])]
    public function promote(User $user, Request $request, UserRepository $userRepository): Response
    {
        $this->checkCsrfToken('admin_user_promote', $user, $request);

        $user->setRoles(['ROLE_ADMIN']);
        $userRepository->save($user, true);
        $this->addFlash('success', 'User promoted to administrator successfully.');

        return $this->redirectToRoute('app_admin_user_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/demote', name: 'app_admin_user_demote', methods: ['POST'])]
    public function demote(User $user, Request $request, UserRepository $userRepository): Response
    {
        $this->checkCsrfToken('admin_user_demote', $user, $request);

        // un administrateur ne peut pas rétrograder son propre compte
        if ($user === $this->getUser()) {
            $this->addFlash('danger', 'You cannot demote your own account.');
            return $this->redirectToRoute('app_admin_user_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        $user->setRoles([]);
        $userRepository->save($user, true);
        $this->addFlash('success', 'User demoted to regular user successfully.');

        return $this->redirectToRoute('app_admin_user_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(
        User $user,
        Request $request,
        UserRepository $userRepository,
        ProjectRepository $projectRepository,
    ): Response {
        $this->checkCsrfToken('admin_user_delete', $user, $request);

        // un administrateur ne peut pas supprimer son propre compte
        if ($user === $this->getUser()) {
            $this->addFlash('danger', 'You cannot delete your own account.');
            return $this->redirectToRoute('app_admin_user_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        // supprime d'abord les projets de l'utilisateur (cascade médias,
        // liens et fichiers), puis le compte lui-même
        foreach ($projectRepository->findBy(['owner' => $user]) as $project) {
            $projectRepository->remove($project);
        }
        $userRepository->remove($user, true);
        $this->addFlash('success', 'User deleted successfully.');

        return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }

    private function checkCsrfToken(string $tokenId, User $user, Request $request): void
    {
        if (!$this->isCsrfTokenValid($tokenId.$user->getId(), $request->request->get('_token'))) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }
    }
}
