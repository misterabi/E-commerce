<?php

namespace App\Controller;

use App\Form\UtilisateurType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateurController extends AbstractController
{
    #[Route('/utilisateur', name: 'app_utilisateur')]
    public function index(): Response
    {
        $user = $this->getUser();
        if($user == null)
            return $this->redirectToRoute('app_login');
        
        $form = $this->createForm(UtilisateurType::class);

        $historique = $user->getPaniers();
        $lstContenuePanier = [];
        foreach($historique as $contenue){
            array_push($lstContenuePanier,$contenue->getContentPaniers());
        }


        return $this->render('utilisateur/index.html.twig', [
            "form" => $form->createView(),
            "user" => $user,
            "historique" => $historique,
            "contenuePanier" => $lstContenuePanier,
        ]);
    }
}
