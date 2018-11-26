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
        $panier = $panierRepo->findBy(array('produit_id'=>$_GET["produit"]));
            if($panier != NULL){

                $repository = $em->getRepository(Panier::class);
                $produitsAmodifier = $repository->findOneBy(['produit_id' => $_GET['produit']]);
                dump($produitsAmodifier);
                $produitsAmodifier->setQuantite($produitsAmodifier->getQuantite()+1);
                $em->persist($produitsAmodifier);
                $em->flush();    // commit des opérations
            }else{

                $panierAdd = new Panier();
                $panierAdd->setQuantite(1);
                $panierAdd->setDateAchat(new \DateTime());
                $user = $this->getUser();
                $panierAdd->setUserId($user->getId());
                $panierAdd->setProduitId($_GET["produit"]);
                $em->persist($panierAdd);
                $em->flush();
            }
        return $this->redirectToRoute('index.index');

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
        $panierRepo = $doctrine->getRepository(Panier::class);
        $em= $this->container->get('doctrine')->getManager();
        $panier= NULL;
        $panier = $panierRepo->findBy(array('produit_id'=>$_GET["produit"]));
    }

}