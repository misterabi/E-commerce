<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Form\UtilisateurUpdateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class UtilisateurController extends AbstractController
{
    #[Route('/utilisateur', name: 'app_utilisateur')]
    public function index(EntityManagerInterface $em,Request $request,TranslatorInterface $trans): Response
    {
        $user = $this->getUser();
        $users = [];
        $users_inscrit = [];
        if($user == null)
            return $this->redirectToRoute('app_login');
        
        if( $user->getRoles()[0] == "ROLE_SUPER_ADMIN"){
            $tmps = $em->getRepository(Utilisateur::class)->findAll();
            foreach($tmps as $utilisateur){
                if($utilisateur->getPaniers()[$utilisateur->getPaniers()->count()-1]->isEtat() == false){
                    array_push($users,[$utilisateur,$utilisateur->getPaniers()[$utilisateur->getPaniers()->count()-1]->getId()]);
                }
                
            }

            //user  avec un panier non validé
            $users = array_values($users);
            
            //uuser inscrit sur la platform
            $users_inscrit = $em->getRepository(Utilisateur::class)->findAll();

            usort($users_inscrit, function($a, $b) {
                return $a->getId() <=> $b->getId();
            });
        }

        $historique = $user->getPaniers();
        $lstContenuePanier = [];
        foreach($historique as $contenue){
            array_push($lstContenuePanier,$contenue->getContentPaniers());
        }

        $form = $this->createForm(UtilisateurUpdateType::class,$user);
        $form->handleRequest($request); 
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($user); 
            $em->flush(); 
            $this->addFlash('success',$trans->trans('flash.success.UpdateProduct'));
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
