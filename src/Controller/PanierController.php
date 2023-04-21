<?php

namespace App\Controller;

use App\Entity\ContentPanier;
use App\Entity\Panier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(Request $request,EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if($user == null){
            return $this->redirectToRoute('app_login');
        }
        $panier = $user->getPaniers()[$user->getPaniers()->count()-1];

        //cree un nouveau panier
        if($panier->isEtat() == true){
            $panier = new Panier();
            $panier->setUtilisateur($user);
            $em->persist($panier);
            $em->flush();
        }

        $contenuePanier = $panier->getContentPaniers();

        return $this->render('panier/index.html.twig', [
            'contenuePanier' => $contenuePanier,
            "show" => false,
        ]);
    }
    
    #[Route('/panier/remove/{id}', name: 'app_panier_remove')]
    public function removeProduct(ContentPanier $contentPanier,EntityManagerInterface $em): Response
    {
        if($contentPanier === null){
            $this->addFlash('danger','Le produit n\'existe pas');
            return $this->redirectToRoute('app_panier');
        }

        $em->remove($contentPanier);
        $em->flush();
        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/historique/{id}', name: 'app_panier_historique')]
    public function historique(Panier $panier,EntityManagerInterface $em):Response
    {
        $user = $this->getUser();
        if($user == null){
            return $this->redirectToRoute('app_login');
        }
        $contenuePanier = $em->getRepository(ContentPanier::class)->findBy(['Panier' => $panier]);
    
        return $this->render('panier/index.html.twig', [
            'contenuePanier' => $contenuePanier,
            'show' => true,
        ]);
    }
}
