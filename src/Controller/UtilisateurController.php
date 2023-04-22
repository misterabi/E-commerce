<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateurController extends AbstractController
{
    #[Route('/utilisateur', name: 'app_utilisateur')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $users = [];
        $users_inscrit = [];
        if($user == null)
            return $this->redirectToRoute('app_login');
        
        if( $user->getRoles()[0] == "ROLE_SUPER_ADMIN"){
            $tmps = $em->getRepository(Utilisateur::class)->findAll();
            foreach($tmps as $utilisateur){
                if($utilisateur->getPaniers()[0]->isEtat() == false){
                    array_push($users,[$utilisateur,$utilisateur->getPaniers()[0]->getId()]);
                }
                
            }
            $users = array_values($users);
            $users_inscrit = $em->getRepository(Utilisateur::class)->findAll();
            usort($users_inscrit, function($a, $b) {
                return $a->getId() <=> $b->getId();
            });


        }
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
            "users" => $users,
            "users_inscrit" => $users_inscrit,
        ]);
    }

    
}
