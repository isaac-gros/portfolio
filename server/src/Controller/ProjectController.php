<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Image;
use App\Entity\Project;

use App\Form\ProjectType;
use App\Form\UploadsType;

class ProjectController extends AbstractController
{
    /**
     * @Route("/add/project", name="add_project")
     */
    public function addProjectAction(Request $request, EntityManagerInterface $em, SluggerInterface $slugger)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $project = new Project();
        $projectForm = $this->createForm(ProjectType::class, $project);
        $projectForm->handleRequest($request);

        $uploadForm = $this->createForm(UploadsType::class);

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
            'uploadForm' => $uploadForm->createView(),
            'formTitle' => 'Nouveau projet',
        ]);
    }
}
