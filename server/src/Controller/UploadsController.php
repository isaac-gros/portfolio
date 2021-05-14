<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use App\Entity\Image;
use App\Form\UploadsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UploadsController extends AbstractController
{

    /**
     * @Route("/add/upload", name="add_upload")
     */
    public function addUploadAction(Request $request, EntityManagerInterface $em, SluggerInterface $slugger)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $uploadForm = $this->createForm(UploadsType::class);

        // Prepare response
        $response = [
            'status' => 500,
            'message' => 'Erreur lors du téléchargement du fichier : ',
            'data' => null
        ];

        if($request->isMethod('POST')) {

            // Submit the form manually with the data sent
            $uploadForm->submit([
                'uploads' => $request->files->get('uploads'),
                '_token' => $request->request->get('_token')
            ]);

            if ($uploadForm->isSubmitted() && $uploadForm->isValid()) {

                // Create entities
                $uploadedFile = $uploadForm->get('uploads')->getData();
                $uploadDir = $this->getParameter('uploads_directory');
                $imageEntities = $this->createEntities($uploadedFile, $uploadDir, $slugger);

                // Add uploaded files entities to response
                if($imageEntities['status'] == 200) {
                    foreach($imageEntities['data'] as $image) {
                        $em->persist($image);
                        $em->flush();
                        $response['data'][] = $image->toArray();
                    }
    
                    $response['status'] = 200;
                    $response['message'] = 'Fichier envoyé avec succès.';
                } else {
                    $response['message'] = $response['message'] . $imageEntities['message'];   
                }

            } else {
                $errors = ($uploadForm->getErrors(true, true));
                foreach($errors as $error) {
                    $response['message'] = $response['message'] . $error->getMessage() . ' ';
                }
            }
        }
        
        return new Response(json_encode($response));

    }

    /**
     * Create entities from ProjectType form 
     * @param UploadedFile|array<UploadedFile> $uploadFile
     * @param string $uploadDir
     * @param SluggerInterface $slugger
     * @return array $response
     */
    public static function createEntities($uploadFile, $uploadDir, $slugger)
    {
        // Default data returned
        $response = [
            'status' => 500,
            'data' => null,
            'message' => 'Une erreur est survenue.'
        ];

        if ($uploadFile) {

            // If there is only one file, convert it to an array 
            // to prevent code repetition
            $uploadedFiles = !is_array($uploadFile) ? [$uploadFile] : $uploadFile;

            foreach ($uploadedFiles as $uploadedFile) {

                // Sanitize the filename
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = uniqid($safeFilename . '_') . '.' . $uploadedFile->guessExtension();

                try {

                    // Upload the file
                    $uploadedFile->move($uploadDir, $newFilename);

                    // Prepare new Image entity in DB
                    $image = new Image();
                    $image->setUrl('/'.'uploads/' . $newFilename);
                    $image->setTitle($originalFilename);

                    if (!is_array($uploadFile)) {
                        $response['data'] = [$image];
                    } else {
                        $response['data'][] = $image;
                    }

                    $response['status'] = 200;
                    $response['message'] = 'Fichiers téléchargés avec succès.';

                } catch (FileException $error) {
                    $response['message'] = $error; // Return error with message for debbug
                }
            }
        }

        return $response;
    }
}