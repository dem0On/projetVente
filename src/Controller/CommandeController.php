<?php

namespace App\Controller;


use App\Entity\Commande;
use App\Entity\Etat;
use App\Entity\LigneCommande;
use App\Entity\Panier;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Produit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Twig\Environment;

class  CommandeController extends Controller{

    /**
     * @Route("/commande/add", name="commande.add")
     */
    public function add(Request $request, Environment $twig, RegistryInterface $doctrine)
    {
        $em= $this->container->get('doctrine')->getManager();
        $produitRepo = $doctrine->getRepository(Produit::class);
        $panierRepo = $doctrine->getRepository(Panier::class);
        $panier = $panierRepo->findBy(array('user_id'=>$this->getUser()->getId()));
        $etatRepo = $doctrine->getRepository(Etat::class);
        $etat= $etatRepo->findBy(array('nom'=>"A préparer"));
        $commande = new Commande();
        $commande->setUserId($this->getUser());
        $commande->setEtatId($etat[0]);
        $commande->setDate(new \DateTime('now'));
        $em->persist($commande);
        $em->flush();

        $commandeClient = $doctrine->getRepository(Commande::class)->findBy(array('user_id'=>$this->getUser()->getId()));
        $lastId = NULL;
        foreach ($commandeClient as $value){
            $lastId = $value;
        }
        foreach($panier as $value){
            $ligne_commande = new LigneCommande();
            $ligne_commande->setProduitId($value->getProduitId());
            $ligne_commande->setQuantite($value->getQuantite());
            $ligne_commande->setCommandeId($lastId);
            $ligne_commande->setPrix($value->getProduitId());

            $supp = $panierRepo->findOneBy(['produit_id' => $value->getProduitId()]);
            $em->remove($supp);
            $em->persist($ligne_commande);
            $em->flush();
        }

        return $this->redirectToRoute('index.index');

    }

    /**
     * @Route("/commande/show", name="commande.showAllCommandes")
     */
    public function showAll(Request $request, Environment $twig, RegistryInterface $doctrine){
        $commandes = $doctrine->getRepository(Commande::class)->findBy(array('user_id'=>$this->getUser()));
        return new Response($twig->render('frontOff/showCommande.html.twig',['lignes' => $commandes]));
    }

    /**
     * @Route("/commmande/consult",name="commande.consult")
     */
    public function consult(Request $request, Environment $twig, RegistryInterface $doctrine){
        $numero = $_GET['idCommande'];
        $detailsCommande = $doctrine->getRepository(LigneCommande::class)->findBy(array('commande_id'=>$_GET['idCommande']));
        return new Response($twig->render('frontOff/consultCommande.html.twig',['lignes' => $detailsCommande]));
    }

    /**
     * @Route("/gestion/showCommande", name="gestion.commande.show")
     */
    public function gestionAll(Request $request, Environment $twig, RegistryInterface $doctrine){
        $commande = $doctrine->getRepository(Commande::class)->findAll();
    return new Response($twig->render('backOff/showAllCommande.html.twig',['commandes' => $commande]));
    }
    /**
    * @Route("/gestion/detailsCommande", name="commande.description")
    */
    public function description(Request $request, Environment $twig, RegistryInterface $doctrine){
        $numero = $_GET['idCommande'];
        $detailsCommande = $doctrine->getRepository(LigneCommande::class)->findBy(array('commande_id'=>$_GET['idCommande']));
        return new Response($twig->render('frontOff/consultCommande.html.twig',['lignes' => $detailsCommande]));
    }

}