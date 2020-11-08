<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Page;
use App\Entity\Project;

use App\Form\PageType;
use App\Form\ProjectType;

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
    public function addProjectAction(Request $request, EntityManagerInterface $em)
    {
        $project = new Project();
        $projectForm = $this->createForm(ProjectType::class, $project);
        $projectForm->handleRequest($request);

        if($projectForm->isSubmitted() && $projectForm->isValid())
        {
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
