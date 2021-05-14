<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Page;

use App\Form\PageType;

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
}
