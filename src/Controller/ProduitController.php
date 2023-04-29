<?php

namespace App\Controller;

use App\Entity\ContentPanier;
use App\Entity\Produit;
use App\Form\AjoutProduitType;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit')]
    public function index(EntityManagerInterface $em,Request $request,SluggerInterface $slugger,TranslatorInterface $trans): Response
    {
        $user = $this->getUser();
        $product = new Produit();
        $form = $this->createForm(ProduitType::class, $product);
        $form->handleRequest($request); 
        if($form->isSubmitted() && $form->isValid() && $user->getRoles()[0] == "ROLE_ADMIN"){
            $imageFile = $form->get('photo')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('warning',$trans->trans("flash.failed.UplaodFile"));
                }
                $product->setPhoto($newFilename);
            }
            $this->addFlash('success',$trans->trans('flash.success.AddProduct'));
            $em->persist($product);
            $em->flush();
        }
        $products = $em->getRepository(Produit::class)->findAll();
        return $this->render('produit/index.html.twig', [
            "form" => $form->createView(),
            "products" => $products
        ]);
    }

    #[Route('/produit/{id}', name: 'app_un_produit')]
    public function Produit(Produit $product=null,EntityManagerInterface $em,Request $request,TranslatorInterface $trans): Response
    {
        if($product === null){
            $this->addFlash('danger',$trans->trans('produit.UndifinedProduct'));
            return $this->redirectToRoute('app_produit');
        }

        //formulaire pour l'ajoute d'un produit au panier avec sa quanité
        $ContentPanier = new contentPanier();
        $formAjouteProduit = $this->createForm(AjoutProduitType::class, $ContentPanier);
        $formAjouteProduit->handleRequest($request);

        $form = null;
        
        if($formAjouteProduit->isSubmitted() && $formAjouteProduit->isValid()){ 
            return $this->redirectToRoute('app_ajouter_produit',
            [
                "id" => $product->getId(),
                "Quantite" => $formAjouteProduit->get('quantite')->getData()
            ]
        );
        }

        if( $this->isGranted('ROLE_ADMIN') or $this->isGranted('ROLE_SUPER_ADMIN')){

            //mise à jours d'une produit par un ADMIN
            $form = $this->createForm(ProduitType::class, $product);
            $form->handleRequest($request); 
            if($form->isSubmitted() && $form->isValid()){
                $em->persist($product); 
                $em->flush(); 
                $this->addFlash('success',$trans->trans('flash.success.UpdateProduct'));

            }
            $form=$form->createView();
        }

        return $this->render('produit/un_produit.html.twig', [
            "form" => $form,
            "formAjouteProduit" => $formAjouteProduit->createView(),
            "product" => $product
        ]);
    }


    #[Route('/produit/ajouter/{id}/{Quantite}', name: 'app_ajouter_produit')]
    public function add_produit(Produit $product=null,EntityManagerInterface $em,Request $request,int $Quantite,TranslatorInterface $trans): Response
    {
        if($product === null){
            $this->addFlash('danger',$trans->trans('flash.failed.UndifinedProduct'));
            return $this->redirectToRoute('app_produit');
        }
        if($Quantite > $product->getStock()){
            $this->addFlash('danger',$trans->trans('flash.failed.unavailable'));
            return $this->redirectToRoute('app_un_produit',
            [
                "id" => $product->getId()
            ]);
        }
        $this->addFlash('success',$trans->trans('flash.success.AddProduct'));

        $user = $this->getUser();
        //on recupere le panier courrant de l'utilisateur
        $LastPanier = $user->getPaniers();
        $LastPanier = $LastPanier[count($LastPanier)-1];
        
        $ContentPanier = new ContentPanier();
        $ContentPanier->setPanier($LastPanier);
        $ContentPanier->addProduit($product);
        $ContentPanier->setQuantite($Quantite);
        $ContentPanier->setDate(new \DateTime());
        $em->persist($ContentPanier);
        $em->flush();
        // $valeurCookie = $request->cookies->get('couleur');
        
        return $this->redirectToRoute('app_produit', [
            "product" => $product,
            "panier" => $LastPanier,
            // "valeurCookie" => $valeurCookie
        ]);
    }

    #[Route('/produit/delete/{id}', name: 'app_delete_produit')]
    public function delete(Produit $product=null,EntityManagerInterface $em,TranslatorInterface $trans): Response
    {
        if($product === null){
            $this->addFlash('danger',$trans->trans('flash.failed.UndifinedProduct'));
            return $this->redirectToRoute('app_produit');
        }

        $em->remove($product); 
        $em->flush(); 
        $this->addFlash('warning',$trans->trans('flash.success.RemoveProduct'));
        return $this->redirectToRoute('app_produit');
    }
}
