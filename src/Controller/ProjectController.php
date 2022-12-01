<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Project;
use App\Form\ProjectType;
use App\Renderer\GalleryRenderer;
use App\Repository\MediaRepository;
use App\Repository\ProjectRepository;
use App\Viewers\GalleryViewer;
use Kibatic\DatagridBundle\Grid\GridBuilder;
use Kibatic\DatagridBundle\Grid\Template;
use Kibatic\DatagridBundle\Grid\Theme;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

#[Route('/project')]
class ProjectController extends AbstractController
{
    #[Route('/', name: 'app_project_index', methods: ['GET'])]
    public function index(
        Request $request,
        ProjectRepository $projectRepository,
        GridBuilder $gridBuilder,
    ): Response {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // get current user
        $user = $this->getUser();

        // create query builder filtered by current user
        $queryBuilder = $projectRepository->createQueryBuilder('p')
            ->where('p.owner = :user')
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC');
        ;
        $grid = $gridBuilder
            ->create($queryBuilder, $request)
            ->setTheme(Theme::BOOTSTRAP5)
            ->addColumn('Name', 'name')
            ->addColumn('Renderer', 'renderer.name')
            ->addColumn(
                'Created at',
                'createdAt',
                Template::DATETIME
            )
            ->addColumn(
                'Actions',
                function (Project $project) {
                    $showButton = [
                        'name' => 'Show',
                        'url' => $this->generateUrl('app_project_show', ['id' => $project->getId()]),
                    ];
                    $editButton = [
                        'name' => 'Edit',
                        'url' => $this->generateUrl('app_project_edit', ['id' => $project->getId()]),
                    ];
                    if ($project->isShareActive()) {
                        $shareButton = [
                            'name' => 'already shared',
                            'url' => "#",
                        ];
                    } else {
                        $shareButton = [
                            'name' => 'Share',
                            'url' => $this->generateUrl('app_share_new', ['id' => $project->getId()]),
                        ];
                    }
                    return [
                        $showButton,
                        $editButton,
                        $shareButton
                    ];
                },
                Template::ACTIONS
            )
            ->getGrid()
        ;

        return $this->render('project/index.html.twig', [
            'grid' => $grid,
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
    public function show(
        Project $project,
        MediaRepository $mediaRepository,
        GridBuilder $gridBuilder,
        Request $request,
        RouterInterface $router
    ): Response {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // get current user
        $user = $this->getUser();

        // deny access if the user is not the owner of the project
        if ($project->getOwner() !== $user) {
            throw $this->createAccessDeniedException();
        }

        // create query builder for Media filtered by current user
        $queryBuilder = $mediaRepository->createQueryBuilder('m')
            ->where('m.project = :project')
            ->setParameter('project', $project)
            ->orderBy('m.createdAt', 'DESC')
        ;
        $grid = $gridBuilder
            ->create($queryBuilder, $request)
            ->setTheme(Theme::BOOTSTRAP5)
            ->addColumn(
                'Media',
                fn(Media $media) => sprintf(
                    '<img src="%s" alt="%s" width="100" />',
                    $router->generate('app_media_thumbnail', [ 'id' => $media->getId() ]),
                    $media->getName()
                ),
                Template::TEXT,
                [ 'escape' => false ]

            )
            ->addColumn(
                'Name',
                'name'
            )
            ->addColumn(
                'Actions',
                fn(Media $media) => [
                    [
                        'name' => 'Show',
                        'url' => $this->generateUrl('app_media_show', ['id' => $media->getId()]),
                    ],
                    [
                        'name' => 'Edit',
                        'url' => $this->generateUrl('app_media_edit', ['id' => $media->getId()]),
                    ]
                ],
                Template::ACTIONS
            )
            ->getGrid()
        ;

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'grid' => $grid,
        ]);
    }

    #[Route('/view/{shareUid}', name: 'app_project_view', methods: ['GET'])]
    public function view(
        Project $project,
        MediaRepository $mediaRepository,
        Request $request
    ): Response {
        $mediaList = $mediaRepository->createQueryBuilder('m')
            ->where('m.project = :project')
            ->setParameter('project', $project)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
        return $this->render('project/view.html.twig', [
            'mediaList' => $mediaList,
            'project' => $project
        ]);
    }

    #[Route('/gallery/{shareUid}', name: 'app_project_gallery', methods: ['GET'])]
    public function gallery(
        Project $project,
        MediaRepository $mediaRepository,
        GalleryRenderer $galleryRenderer,
        Request $request
    ): Response {
        $mediaList = $mediaRepository->createQueryBuilder('m')
            ->where('m.project = :project')
            ->setParameter('project', $project)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        $items = $galleryRenderer->getItems($project);

        return $this->render('project/gallery.html.twig', [
            'mediaList' => $mediaList,
            'project' => $project,
            'items' => $items
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
