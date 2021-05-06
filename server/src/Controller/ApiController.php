<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends AbstractController
{
    /**
     * Retrieve uploaded images
     * @Route("/api/uploads/", name="getUploads", methods={"GET"})
     */
    public function getUploadsAction(EntityManagerInterface $em): Response
    {
        $res = [];
        $query = (!empty($request)) ? $request->query->all() : [];
        $uploads = $em->getRepository('App\Entity\Image')->findBy($query, null, 20);

        foreach ($uploads as $upload) {
            $res[] = $upload->toArray();
        }

        $response = new Response();
        $response->setContent(json_encode($res));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}