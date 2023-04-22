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

class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit')]
    public function index(EntityManagerInterface $em,Request $request,SluggerInterface $slugger): Response
    {
        $product = new Produit();
        $form = $this->createForm(ProduitType::class, $product);
        $form->handleRequest($request); 
        if($form->isSubmitted() && $form->isValid()){
            $imageFile = $form->get('photo')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash('warning','Erreur lors de l\'upload de l\'image');
                }

                // updates the 'imageFilename' property to store the PDF file name
                // instead of its contents
                $product->setPhoto($newFilename);
            }
            $this->addFlash('success','Le produit a bien été ajouté a la liste des produits');
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
    public function Produit(Produit $product=null,EntityManagerInterface $em,Request $request): Response
    {
        if($product === null){
            $this->addFlash('danger','Le produit n\'existe pas');
            return $this->redirectToRoute('app_produit');
        }

        //formulaire pour l'ajoute d'un produit au panier avec sa quanité
        $ContentPanier = new contentPanier();
        $formAjouteProduit = $this->createForm(AjoutProduitType::class, $ContentPanier);
        $formAjouteProduit->handleRequest($request);
        
        if($formAjouteProduit->isSubmitted() && $formAjouteProduit->isValid()){
            $em->persist($product); 
            $em->flush(); 
            $this->addFlash('success','Le produit a bien été ajouté au panier');
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
                $this->addFlash('success','Le produit a bien été mise à jour');

            }
        }

        return $this->render('produit/un_produit.html.twig', [
            "form" => $form->createView(),
            "formAjouteProduit" => $formAjouteProduit->createView(),
            "product" => $product
        ]);
    }


    #[Route('/produit/ajouter/{id}/{Quantite}', name: 'app_ajouter_produit')]
    public function add_produit(Produit $product=null,EntityManagerInterface $em,Request $request,int $Quantite): Response
    {
        if($product === null){
            $this->addFlash('danger','Le produit n\'existe pas');
            return $this->redirectToRoute('app_produit');
        }

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
        
        return $this->redirectToRoute('app_produit', [
            "product" => $product,
            "panier" => $LastPanier
        ]);
    }

    #[Route('/produit/delete/{id}', name: 'app_delete_produit')]
    public function delete(Produit $product=null,EntityManagerInterface $em): Response
    {
        if($product === null){
            $this->addFlash('danger','Le produit n\'existe pas');
            return $this->redirectToRoute('app_produit');
        }

        $em->remove($product); 
        $em->flush(); 
        $this->addFlash('warning','Le produit a bien été supprimé');
        return $this->redirectToRoute('app_produit');
    }
}
