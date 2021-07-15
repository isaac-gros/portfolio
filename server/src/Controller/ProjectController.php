<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

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

        // Handle POST request
        if($request->isMethod('POST') && !empty($request->request->get('project')))
        {
            $formData = $request->request->get('project');
            $thumbnailId = intval($formData['thumbnail']);
            $galleryImagesId = array_map('intval', explode(',', $formData['images']));
            $thumbnail = $em->getRepository('App\Entity\Image')->find($thumbnailId);
            $gallery = $em->getRepository('App\Entity\Image')->findBy(['id' => $galleryImagesId]);
            
            // Set base infos
            $project->setTitle($formData['base']['title']);
            $project->setDescription($formData['base']['description']);
            $project->setContent($formData['base']['content']);

            // Set project details
            $project->setName($formData['name']);
            $project->setSummary($formData['summary']);
            $project->setThumbnail($thumbnail);
            $project->setImages($gallery);
 
            // Send everything packed up to DB
            $em->persist($project);
            $em->flush();

            $thumbnail->setAsProjectThumbnail($project);
            foreach($gallery as $projectImage) {
                $projectImage->setAsProjectImage($project);
                $em->persist($projectImage);
                $em->flush();
            }

            return $this->redirectToRoute('dashboard');
        }

        $uploadForm = $this->createForm(UploadsType::class);

        return $this->render('dashboard/editor.html.twig', [
            'entityForm' => $projectForm->createView(),
            'entityType' => 'project',
            'uploadForm' => $uploadForm->createView(),
            'formTitle' => 'Nouveau projet',
        ]);
    }
}
