<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            $cookie = new Cookie('Payement', 'false', time() + 3600);
            $rep = $this->redirectToRoute('app_produit');
            $rep->headers->setCookie($cookie);
            return $rep;
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $cookie = new Cookie('Payement', 'false', time() + 3600);
        $rep = $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
        $rep->headers->setCookie($cookie);
        return $rep;
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): Response
    {
        $res = $this->render('produit/index.html.twig');
        $res->headers->clearCookie('Payement');
        return $res;
    }
}
