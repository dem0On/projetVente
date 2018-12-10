<?php

namespace App\Controller;

use App\Entity\Panier;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


use App\Entity\Produit;
use App\Form\ProduitType;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;


use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Twig\Environment;                            // template TWIG
use Symfony\Bridge\Doctrine\RegistryInterface;   // ORM Doctrine
use Symfony\Component\HttpFoundation\Request;    // objet REQUEST
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
// dans les annotations @Method

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;  // annotation security

/**
 * Class GestionCommandeController
 * @package App\Controller
 * @Route(name="", path="/admin")
 * @Security("has_role('ROLE_ADMIN')")
 */

class ProduitController extends Controller
{
    /**
     * @Route("/produit", name="produit")
     */
    public function index()
    {
        return $this->redirectToRoute('produit.show');
    }




    /**
     * @Route("/produit/add", name="produit.add")
     */
    public function addProduit(Request $request, Environment $twig, RegistryInterface $doctrine, FormFactoryInterface $formFactory)
    {
        $form=$formFactory->createBuilder(ProduitType::class)->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $produit=$form->getData();
            $doctrine->getEntityManager()->persist($produit);
            $doctrine->getEntityManager()->flush();
            return $this->redirectToRoute('produit.show');
        }
        return new Response($twig->render('backOff/Produit/formProduit.html.twig',['form'=>$form->createView()]));
    }

    /**
     * @Route("/produit/delete", name="produit.delete")
     */
    public function deleteProduit(Request $request, Environment $twig, RegistryInterface $doctrine, FormFactoryInterface $formFactory)
    {
        $produit=$doctrine->getRepository(Produit::class)->find($request->query->get('id'));
        $doctrine->getEntityManager()->remove($produit);
        $doctrine->getEntityManager()->flush();
        return $this->redirectToRoute('produit.show');
    }

    /**
     * @Route("/produit/edit", name="produit.edit")
     */
    public function editProduit(Request $request, Environment $twig, RegistryInterface $doctrine, FormFactoryInterface $formFactory)
    {
        $produit=$doctrine->getRepository(Produit::class)->find($request->query->get('id'));
        $form=$formFactory->createBuilder(ProduitType::class,$produit)->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getEntityManager()->flush();
            return $this->redirectToRoute('produit.show');
        }
        return new Response($twig->render('backOff/Produit/formProduit.html.twig',['form'=>$form->createView()]));
    }

    /**
     * @Route("/produit/show",name="produit.show")
     */
    public function showProduit(Request $request, Environment $twig, RegistryInterface $doctrine, FormFactoryInterface $formFactory){
        $produit = $doctrine->getRepository(Produit::class)->findAll();
        $stocks = $doctrine->getRepository(Produit::class)->findBy(array('stock'=>0,'stock'=>null));
        dump($stocks);
        return new Response($twig->render('backOff/Produit/showProduits.html.twig',['produits'=>$produit,'stocks'=>$stocks]));
    }
    /**
     * @Route("/produit/addStock", name="produit.addStock")
     */
    public function addStock(Request $request, Environment $twig, RegistryInterface $doctrine, FormFactoryInterface $formFactory){
        $produit = $doctrine->getRepository(Produit::class)->find($request->query->get('id'));
        $form = $this->createFormBuilder()
            ->add('stock', IntegerType::class)
            ->add('submit',SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em = $this->container->get('doctrine')->getManager();
            $data = $form->getData();
            $produit->setStock($data['stock']);
            $em->persist($produit);
            $em->flush();
            return $this->redirectToRoute('produit.show');
        }
        return new Response($twig->render('backOff/Produit/addStock.html.twig',['form'=>$form->createView()]));
    }
}
