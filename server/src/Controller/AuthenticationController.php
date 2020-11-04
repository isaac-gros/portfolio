<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthenticationController extends AbstractController
{

    /**
     * @Route("/register", name="app_auth")
     */
    public function register(
        Request $request, 
        UserPasswordEncoderInterface $passwordEncoder,
        GoogleAuthenticatorInterface $googleAuthenticatorInterface
    ): Response
    {
        $registrationAllowed = boolval($_ENV['REGISTRATION_ALLOWED']);
        if(!$registrationAllowed) {
            return new Response($this->renderView('errors/error.html.twig', [
                'status' => 403,
                'message' => 'La création de nouveaux utilisateurs est désactivée.'
            ]), 403);
        }

        $user = new User();
        $signInForm = $this->createForm(RegistrationFormType::class, $user);
        $signInForm->handleRequest($request);

        if($signInForm->isSubmitted() && $signInForm->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $signInForm->get('plainPassword')->getData()
                )
            );

            $secret = $googleAuthenticatorInterface->generateSecret();
            $user->setGoogleAuthenticatorSecret($secret);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('authentication/authentication.html.twig', [
            'registrationForm' => $signInForm->createView(),
        ]);
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }

    /**
     * @Route("/2fa", name="2fa_login")
     */
    public function twoFactorLogin()
    {
        return $this->render('security/2fa_login.html.twig');
    }
}
