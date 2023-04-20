<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            return $this->redirectToRoute('app_app_produit');
        }

        $form = $this->createForm(ProduitType::class, $product);
        $form->handleRequest($request); 
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($product); 
            $em->flush(); 
        }
        return $this->render('produit/un_produit.html.twig', [
            "form" => $form->createView(),
            "product" => $product
        ]);
    }
}
