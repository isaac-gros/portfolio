<?php

namespace App\Controller;

use App\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Page;
use App\Entity\Project;

use App\Form\PageType;
use App\Form\ProjectType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // List of quick actions
        $actions = [
            [
                'icon' => 'home',
                'text' => 'Éditer l\'accueil',
                'path' => '#'
            ],
            [
                'icon' => 'file',
                'text' => 'Nouvelle page',
                'path' => '#'
            ],
            [
                'icon' => 'tasks',
                'text' => 'Nouveau projet',
                'path' => '#'
            ],
            [
                'icon' => 'music',
                'text' => 'Modifier la playlist',
                'path' => '#'
            ]
        ];

        // List of content overviews
        $overviews = [
            [
                'title' => 'Pages',
                'items' => []
            ],
            [
                'title' => 'Projets',
                'items' => []
            ],
            [
                'title' => 'Playlist actuelle',
                'items' => []
            ]
        ];

        return $this->render('dashboard/index.html.twig', [
            'actions' => $actions,
            'overviews' => $overviews
        ]);
    }

    /**
     * @Route("/add/page", name="add_page")
     */
    public function addPageAction(Request $request, EntityManagerInterface $em)
    {
        $page = new Page();
        $pageForm = $this->createForm(PageType::class, $page);
        $pageForm->handleRequest($request);

        // Handle POST request
        if($pageForm->isSubmitted() && $pageForm->isValid())
        {
            $em->persist($page);
            $em->flush();
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('dashboard/editor.html.twig', [
            'entityForm' => $pageForm->createView(),
            'entityType' => 'page',
            'formTitle' => 'Nouvelle page',
        ]);
    }

    /**
     * @Route("/add/project", name="add_project")
     */
    public function addProjectAction(Request $request, EntityManagerInterface $em, SluggerInterface $slugger)
    {
        $project = new Project();
        $projectForm = $this->createForm(ProjectType::class, $project);
        $projectForm->handleRequest($request);

        // Handle POST request
        if($projectForm->isSubmitted() && $projectForm->isValid())
        {
            // Process thumbnail upload
            $thumbnail = $projectForm->get('thumbnail')->getData();

            if ($thumbnail) {
                $originalFilename = pathinfo($thumbnail->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = uniqid($safeFilename.'_').'.'.$thumbnail->guessExtension();

                try {
                    $thumbnail->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );

                    // Create new Image in DB
                    $image = new Image();
                    $image->setUrl('uploads/' . $newFilename);
                    $image->setTitle($originalFilename);
                    $image->setProject($project);

                } catch (FileException $e) {
                    return new Response($this->renderView('errors/error.html.twig', [
                        'status' => 500,
                        'message' => $e->getMessage()
                    ]), 500);
                }
            }

            if(isset($image)) {
                $em->persist($image);
                $project->setThumbnail($image->getUrl());
            }
            
            $em->persist($project);
            $em->flush();
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('dashboard/editor.html.twig', [
            'entityForm' => $projectForm->createView(),
            'entityType' => 'project',
            'formTitle' => 'Nouveau projet',
        ]);
    }
}
