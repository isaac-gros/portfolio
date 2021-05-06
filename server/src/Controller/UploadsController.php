<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use App\Entity\Image;

class UploadsController extends AbstractController
{

    /**
     * Create entities from ProjectType form 
     * @param UploadedFile|array<UploadedFile> $data
     * @param string $uploadDir
     * @param SluggerInterface $slugger
     * @return array $response
     */
    public static function createEntities($data, $uploadDir, $slugger)
    {
        // Default data returned
        $response = [
            'data' => null,
            'message' => null
        ];

        if ($data) {

            // If there is only one file, convert it to an array 
            // to prevent code repetition
            $uploadedFiles = !is_array($data) ? [$data] : $data;

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

                    if (!is_array($data)) {
                        $response['data'] = $image;
                    } else {
                        $response['data'][] = $image;
                    }
                } catch (FileException $error) {
                    $response['message'] = $error; // Return error with message for debbug
                }
            }
        }

        return $response;
    }
}