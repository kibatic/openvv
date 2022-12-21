<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Project;
use App\Entity\User;
use App\ExportImport\Exporter;
use App\ExportImport\Importer;
use App\Form\ImportType;
use App\Form\ProjectType;
use App\Repository\MediaRepository;
use App\Repository\ProjectRepository;
use Kibatic\DatagridBundle\Grid\GridBuilder;
use Kibatic\DatagridBundle\Grid\Template;
use Kibatic\DatagridBundle\Grid\Theme;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Uid\Uuid;

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
            ->addColumn(
                'Name',
                function (Project $project) {
                    if ($project->isShareActive()) {
                        return sprintf(
                            '%s<br/><span class="text-secondary">shared until %s</span>',
                            $project->getName(),
                            $project->getShareEndedAt()->format('Y-m-d'),
                        );
                    }
                    return sprintf(
                        '%s<br/><span class="text-secondary">Not shared</span>',
                        $project->getName(),
                    );
                },
                Template::TEXT,
                ['escape' => false],
            )
            ->addColumn('Renderer', 'renderer.name')
            ->addColumn(
                'Created at',
                fn(Project $project) => $project->getCreatedAt()->format('Y-m-d'),
                Template::TEXT
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
                    $previewButton = [
                        'name' => 'Preview',
                        'url' => $this->generateUrl('app_renderer_preview', ['id' => $project->getId()]),
                    ];
                    return [
                        $showButton,
                        //$editButton,
                        $previewButton
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
            $this->addFlash('success', 'Project created successfully.');
            return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('project/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/import', name: 'app_project_import', methods: ['GET', 'POST'])]
    public function import(
        Request $request,
        Importer $importer
    ): Response
    {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // get current user
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ImportType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['import_file']->getData();
            $extension = $file->guessExtension();
            if (!$extension) {
                $extension = 'zip';
            }
            $dir = sys_get_temp_dir();
            $filename = Uuid::v4() . '.' . $extension;
            $file->move($dir, $filename);
            $zipFile = $dir . '/' . $filename;
            $project = $importer->importProject($zipFile, $user);
            $filesystem = new Filesystem();
            $filesystem->remove($zipFile);
            $this->addFlash('success', 'Project imported successfully.');
            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('project/import.html.twig', [
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
            ->orderBy('m.orderInProject', 'ASC')
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
                'Infos',
                fn(Media $media) => sprintf(
                    '
                    order: %s<br/>
                    from links : %s<br/>
                    to links : %s
                    ',
                    $media->getOrderInProject(),
                    count($media->getFromMeLinks()),
                    count($media->getToMeLinks())
                ),
                Template::TEXT,
                [ 'escape' => false ]
            )
            ->addColumn(
                'Actions',
                fn(Media $media) => [
                    [
                        'name' => 'Show',
                        'url' => $this->generateUrl('app_media_show', ['id' => $media->getId()]),
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


    #[Route('/{id}/export', name: 'app_project_export', methods: ['GET'])]
    public function export(
        Project $project,
        Exporter $exporter,
        Request $request,
        RouterInterface $router
    ): Response
    {
        // deny access if the user is not logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // get current user
        $user = $this->getUser();

        // deny access if the user is not the owner of the project
        if ($project->getOwner() !== $user) {
            throw $this->createAccessDeniedException();
        }

        return $exporter->streamResponse($project);
    }


    #[Route('/{id}/edit', name: 'app_project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, ProjectRepository $projectRepository): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $projectRepository->save($project, true);
            $this->addFlash('success', 'Project updated successfully.');
            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()], Response::HTTP_SEE_OTHER);
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
