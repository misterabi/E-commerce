<?php

namespace App\Controller;

use App\Entity\ContentPanier;
use App\Entity\Panier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

class StripeController extends AbstractController
{
    #[Route('/stripe', name: 'app_stripe')]
    public function index(): Response
    {
        $user = $this->getUser();
        if($user == null){
            return $this->redirectToRoute('app_login');
        }
        return $this->render('stripe/index.html.twig', [
        ]);
    }


    #[Route('/stripe/payement', name: 'stripe_payement')]
    public function payement(EntityManagerInterface $em)
    {
        //recuper la clé api
        $stripeSecretKey = $this->getParameter("stripe_sk");
        //initialisation de l'aip stripe
        \Stripe\Stripe::setApiKey($stripeSecretKey);
        $total = 0;
        // Récupérer la liste des produits du panier en cours (non payé)

        $user = $this->getUser();
        if($user == null){
            return $this->redirectToRoute('app_login');
        }
        $panier = $user->getPaniers()[$user->getPaniers()->count()-1];
        $panier = $em->getRepository(Panier::class)->findOneBy(['Utilisateur' => $user, 'DateAchat' => null]);
        $contenuePanier = $panier->getContentPaniers();
        

        foreach($contenuePanier as $contentPanier){
            foreach($contentPanier->getProduit() as $produit ){
                $total += $produit->getPrix() * $contentPanier->getQuantite();
            }
        }
        $total = $total * 100;

        
        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $total,
                'currency' => 'eur',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);
            
            if($panier->getIdPayement() == null){
                // Stocker en base dans le panier l'id unique donc ajouter dans l'entity panier un attribut idStripe puis aller dans sucess par rapport a cette id
                $em->persist($panier->setIdPayement($paymentIntent->id));
                $em->flush();
            }
            
            
            $output = [
                "payement Intent" => $paymentIntent,
                'clientSecret' => $paymentIntent->client_secret,
            ];

            return new JsonResponse($output);
        } catch (\Error $e) {
            return new JsonResponse(['error' => $e->getMessage()],500);
        }
        
    }



    #[Route('/stripe/success/{id}', name: 'stripe_success')]
    public function success(EntityManagerInterface $em, $id)
    {
        // Récupère en base un panier avec l'utilisateur et l'id unique
        // Si le panier est non payé -> passe en payé

        $user = $this->getUser();
        if($user == null){
            return $this->redirectToRoute('app_login');
        }
        $panier = $em->getRepository(Panier::class)->findOneBy(['Utilisateur' => $user, 'DateAchat' => null]);
        if($panier == null){
            return $this->redirectToRoute('app_produit');
        }
        if($panier->getIdPayement() != $id){
            return $this->render('stripe/failed.html.twig',[
                "panierid" => $panier->getIdPayement(),
                "id" => $id
            ]);
        }

        //update de la quantité des produits $user->getPaniers()[$user->getPaniers()->count()-1]->getContentPaniers()
        $content = $em->getRepository(ContentPanier::class)->findBy(['Panier' => $panier]);
        foreach($content as $contentPanier){
            foreach($contentPanier->getProduit() as $produit){

                $produit->setStock($produit->getStock() - $contentPanier->getQuantite());
                $em->persist($produit);
            }
        }
        $panier->setEtat(true);
        $panier->setDateAchat(new \DateTime());
        $em->persist($panier);
        $em->flush();
        $res = $this->render('stripe/success.html.twig');
        $res->headers->clearCookie('Payement');
        $cookie = new Cookie('Payement', 'false', time() + 3600);
        $res->headers->setCookie($cookie);
        return $res;
    }

    #[Route('/stripe/failed', name: 'stripe_failed')]
    public function failed(EntityManagerInterface $em)
    {
        $user = $this->getUser();
        if($user == null){
            return $this->redirectToRoute('app_login');
        }
        $panier = $em->getRepository(Panier::class)->findOneBy(['Utilisateur' => $user, 'DateAchat' => null]);
        $panier->setIdPayement(null);
        $em->persist($panier);
        $em->flush();
        return $this->render('stripe/failed.html.twig');
    }

}
