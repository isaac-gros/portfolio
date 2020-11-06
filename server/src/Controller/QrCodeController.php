<?php

namespace App\Controller;

use Scheb\TwoFactorBundle\Security\TwoFactor\QrCode\QrCodeGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QrCodeController extends AbstractController
{
    /**
     * @Route("/qr-code", name="qr_code")
     */
    public function displayGoogleAuthenticatorQrCode(QrCodeGenerator $qrCodeGenerator)
    {
        if(empty($this->getUser())) {
            return new Response($this->renderView('errors/error.html.twig', [
                'status' => 403,
                'message' => 'Vous devez être connecté pour accéder à ce contenu.'
            ]), 403);
        }
        $qrCode = $qrCodeGenerator->getGoogleAuthenticatorQrCode($this->getUser());
        $qrCode->setSize(160);
        return new Response($qrCode->writeString(), 200, ['Content-Type' => 'image/png']);
    }
}