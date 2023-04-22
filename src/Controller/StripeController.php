<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

class StripeController extends AbstractController
{
    #[Route('/stripe/{somme}', name: 'app_stripe')]
    public function index(int $somme): Response
    {
        $user = $this->getUser();
        if($user == null){
            return $this->redirectToRoute('app_login');
        }
        return $this->render('stripe/index.html.twig', [
           "total" => $somme,
        ]);
    }

    #[Route('/stripe/payement', name: 'stripe_payement')]
    public function payement()
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
        
            $output = [
                "payement Intent" => $paymentIntent,
                'clientSecret' => $paymentIntent->client_secret,
            ];
            return new JsonResponse($output);
        } catch (\Error $e) {
            return new JsonResponse(['error' => $e->getMessage()],500);
        }
        
    }



    #[Route('/stripe/success', name: 'stripe_success')]
    public function success(EntityManagerInterface $em)
    {
        $user = $this->getUser();
        if($user == null){
            return $this->redirectToRoute('app_login');
        }
        $panier = $user->getPaniers()[$user->getPaniers()->count()-1];
        $panier->setEtat(true);
        $panier->setDateAchat(new \DateTime());
        $em->persist($panier);
        $em->flush();
        return $this->render('stripe/success.html.twig');
    }

    #[Route('/stripe/failed', name: 'stripe_failed')]
    public function failed()
    {
        return $this->render('stripe/failed.html.twig');
    }

}
