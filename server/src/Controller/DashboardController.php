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
    public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // List of quick actions
        $actions = [
            [
                'icon' => 'home',
                'text' => 'Éditer l\'accueil',
                'path' => 'dashboard'
            ],
            [
                'icon' => 'file',
                'text' => 'Nouvelle page',
                'path' => 'add_page'
            ],
            [
                'icon' => 'tasks',
                'text' => 'Nouveau projet',
                'path' => 'add_project'
            ],
            [
                'icon' => 'music',
                'text' => 'Modifier la playlist',
                'path' => 'dashboard'
            ]
        ];

        // List of content overviews
        $overviews = [
            [
                'title' => 'Pages',
                'items' => $em->getRepository('App\Entity\Page')->findAll(
                    [], ['id' => 'desc'], 5
                ),
            ],
            [
                'title' => 'Projets',
                'items' => $em->getRepository('App\Entity\Project')->findAll(
                    [], ['id' => 'desc'], 5
                ),
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $project = new Project();
        $projectForm = $this->createForm(ProjectType::class, $project);
        $projectForm->handleRequest($request);

        // Handle POST request
        if($projectForm->isSubmitted() && $projectForm->isValid())
        {
            // Process thumbnail upload
            $thumbnail = $projectForm->get('thumbnail')->getData();
            $gallery = $projectForm->get('images')->getData();

            // Prepare entities for submitted thumbnail and project images
            $uploadDir = $this->getParameter('uploads_directory');
            $thumbnailImage = UploadsController::createEntities($thumbnail, $uploadDir, $slugger);
            $galleryImages = UploadsController::createEntities($gallery, $uploadDir, $slugger);

            // Set the project of the uploaded images 
            if($thumbnailImage['data'] != null) {
                $thumbnailImage['data']->setProject($project);
                $em->persist($thumbnailImage['data']);
                $project->setThumbnail($thumbnailImage['data']->getUrl());
            }

            if($galleryImages['data'] != null) {
                foreach($galleryImages['data'] as $projectImage) {
                    $projectImage->setProject($project);
                    $em->persist($projectImage);
                }
            }
            
            // Send everything packed up to DB
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
