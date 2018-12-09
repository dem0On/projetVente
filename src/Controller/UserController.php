<?php

namespace App\Controller;


use App\Entity\Panier;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Produit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Validator\Constraints\Email;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends Controller{
    /**
     * @Route("/user/new", name="user.add")
     */
    public function addNewUser(Request $request, Environment $twig, RegistryInterface $doctrine){
        $form = $this->createFormBuilder()
            ->add('pseudo', TextType::class)
            ->add('email', Email::class)
            ->add('password', PasswordType::class)
            ->add('submit', SubmitType::class)
            ->getForm();
        return new Response($twig->render('security/formUser.html.twig',['form'=>$form->createView()]));
    }

    /**
     * @Route("/user/modifyPs", name="user.modifyClient")
     */
    public function modifyClient(Request $request, Environment $twig, RegistryInterface $doctrine){
        $info = $doctrine->getRepository(User::class)->find($this->getUser()->getId());
        $form = $this->createFormBuilder()
            ->add('pseudo',TextType::class,array('data'=>$info->getUsername()))
            ->add('email',EmailType::class,array('data'=>$info->getEmail()))
            ->add('submit',SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->container->get('doctrine')->getManager();
            $info = $doctrine->getRepository(User::class)->find($this->getUser()->getId());
            $data = $form->getData();
            $info->setEmail($data['email']);
            $info->setUsername($data['pseudo']);
            $em->persist($info);
            $em->flush();
            return $this->redirectToRoute('user_registration_complete');
        }
        return new Response($twig->render('frontOff/modifyClient.html.twig',['form'=>$form->createView()]));
    }
    /**
     * @Route("/user/modifyPassword", name="user.modifyPassword")
     */
    public function modifyPassword(Request $request, Environment $twig, RegistryInterface $doctrine,UserPasswordEncoderInterface $encoder){
        $form = $this->createFormBuilder()
            ->add('password',PasswordType::class)
            ->add('submit',SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->container->get('doctrine')->getManager();
            $info = $doctrine->getRepository(User::class)->find($this->getUser()->getId());
            $data = $form->getData();
            $password = $encoder->encodePassword($info,$data['password']);
            $info->setPassword($password);
            $em->persist($info);
            $em->flush();
            return $this->redirectToRoute('user_registration_complete');
        }
        return new Response($twig->render('frontOff/modifyPassword.html.twig',['form'=>$form->createView()]));
    }

    /**
     * @Route("/gestion/showUser", name="gestion.user.show")
     */
    public function showAll(Request $request, Environment $twig, RegistryInterface $doctrine,UserPasswordEncoderInterface $encoder)
    {
        $users = $doctrine->getRepository(User::class)->findAll();
        return new Response($twig->render('backOff/showClient.html.twig',['users'=>$users]));
    }
}