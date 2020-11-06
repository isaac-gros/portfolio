<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
}
