<?php

namespace App\Controller;


use App\Entity\Panier;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Produit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Twig\Environment;


class PanierController extends Controller
{

    /**
     * @Route("/panier", name="panier")
     */
    public function index()
    {
        return $this->redirectToRoute('panier.show');
    }

    /**
     * @Route("/panier/add",name="panier.add")
     */
    public function add(Request $request, Environment $twig, RegistryInterface $doctrine){
        $panierRepo = $doctrine->getRepository(Panier::class);
        $em= $this->container->get('doctrine')->getManager();
        $panier= NULL;
        $produit_id = $request->get('produit');
        $produit_id = intval($produit_id);
        $produitRepo = $doctrine->getRepository(Produit::class);
        $stockproduit = $produitRepo->find($_GET["produit"]);
        $produit = $produitRepo->find($produit_id);
        $panier = $panierRepo->findBy(array('produit_id'=>$_GET["produit"],'user_id'=>$this->getUser()->getId()));
        if($stockproduit->getstock() > 0) {
            if ($panier != NULL) {

                $repository = $em->getRepository(Panier::class);
                $produitsAmodifier = $repository->findOneBy(array('produit_id' => $_GET['produit'], 'user_id' => $this->getUser()->getId()));
                dump($produitsAmodifier);
                $produitsAmodifier->setQuantite($produitsAmodifier->getQuantite() + 1);
                $em->persist($produitsAmodifier);
                $em->flush();    // commit des opérations
            } else {

                $panierAdd = new Panier();
                $panierAdd->setQuantite(1);
                $panierAdd->setDateAchat(new \DateTime());
                $user = $this->getUser();
                $panierAdd->setUserId($user->getId());
                $panierAdd->setProduitId($produit);
                $em->persist($panierAdd);
                $em->flush();
            }
            $stockproduit->setStock($stockproduit->getStock()-1);
            $em->persist($stockproduit);
            $em->flush();
            return $this->redirectToRoute('index.index');
        }else{
            return $this->redirectToRoute('index.index');
        }
    }

    /**
     * @Route("/panier/show",name="panier.show")
     */
    public function show(Request $request,Environment $twig, RegistryInterface $doctrine){

    }

    /**
     * @Route("/panier/del", name="panier.del")
     */
    public function del(Request $request,Environment $twig, RegistryInterface $doctrine)
    {
        $panierrepo = $doctrine->getRepository(Panier::class);
        $produitrepo = $doctrine->getRepository(Produit::class);
        $em = $this->container->get('doctrine')->getManager();
        $panier = NULL;
        $stockproduit = $produitrepo->find($_GET['produit']);

        $panier = $panierrepo->findBy(array('produit_id' => $_GET['produit'], 'user_id' => $this->getUser()->getId()));
        dump($panier);

            if ($panier[0]->getQuantite() <= 1) {
                $repository = $em->getRepository(Panier::class);
                $supp = $repository->findOneBy(['produit_id' => $_GET['produit']]);
                dump($supp);
                $em->remove($supp);
                $em->flush($supp);
            } else {
                $panier[0]->setQuantite($panier[0]->getQuantite() - 1);
                $em->persist($panier[0]);
                $em->flush();    // commit de
            }
            $stockproduit->setStock($stockproduit->getStock() + 1);
            $em->persist($stockproduit);
            $em->flush();

            return $this->redirectToRoute('index.index');

    }

    /**
     * @Route("/panier/valid", name="panier.valid")
     */
    public function validPanier(Request $request,Environment $twig, RegistryInterface $doctrine){

        return new Response($twig->render('frontOff/validCommande.html.twig'));
    }

}