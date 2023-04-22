<?php

namespace App\Controller;

use App\Entity\ContentPanier;
use App\Entity\Panier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(Request $request,EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if($user == null){
            return $this->redirectToRoute('app_login');
        }
        //Si il a pas de panier on en cree un
        if($user->getPaniers() == null){
            $panier = new Panier();
            $panier->setUtilisateur($user);
            $em->persist($panier);
            $em->flush();
        }

        $panier = $user->getPaniers()[$user->getPaniers()->count()-1];

        //cree un nouveau panier si le panier precedent est cloturer
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
    public function removeProduct(ContentPanier $contentPanier,EntityManagerInterface $em,TranslatorInterface $trans): Response
    {
        $user = $this->getUser();
        if($user == null){
            return $this->redirectToRoute('app_login');
        }
        if($contentPanier === null){
            $this->addFlash('danger',$trans->trans("flash.failed.UndifinedProduct"));
            return $this->redirectToRoute('app_panier');
        }

        $em->remove($contentPanier);
        $em->flush();
        $this->addFlash('danger',$trans->trans("flash.success.RemoveProduct"));
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
