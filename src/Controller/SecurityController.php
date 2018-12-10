<?php
// src/Controller/SecurityController.php
namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authUtils)
    {
        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/registration", name="registration")
     */
    public function addNewUser(Request $request, Environment $twig, RegistryInterface $doctrine,UserPasswordEncoderInterface $encoder){
        $form = $this->createFormBuilder()
            ->add('pseudo', TextType::class)
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('submit', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->container->get('doctrine')->getManager();
            $data = $form->getData();
            $user = new User();
            $password = $encoder->encodePassword($user,$data['password']);
            $user->setRoles('ROLE_CLIENT');
            $user->setUsername($data['pseudo']);
            $user->setPassword($password);
            $user->setEmail($data['email']);
            $user->setIsActive('1');
            dump($user);
            $em->persist($user);
            $em->flush();
            $message = (new \Swift_Message('hello Email'))
                ->setSubject('Confirmation inscription')
                ->setFrom('dem0onn70290@gmail.com')
                ->setTo($user->getEmail())
                ->setContentType('text/html')
                ->setBody('Bonjour, merci pour votre inscription sur notre site. Vous pouvez commencez à acheter via ce lien :');
            $this->get('mailer')->send($message);
            return $this->redirectToRoute('index.index');
        }
        return new Response($twig->render('security/formUser.html.twig',['form'=>$form->createView()]));
    }

    /**
     * @Route("/showRegistration", name="user_registration_complete")
     */
    public function showInfo(Request $request, Environment $twig, RegistryInterface $doctrine){
        $infoPerso = $doctrine->getRepository(User::class)->findBy(array('id'=>$this->getUser()->getId()));
        return new Response($twig->render('frontOff/showInformations.html.twig',['infos'=>$infoPerso]));
    }
    /**
     * @Route("/changePassword", name="changePassword")
     */
    public function changePass(Request $request, Environment $twig, RegistryInterface $doctrine,UserPasswordEncoderInterface $encoder){
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('submit', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->container->get('doctrine')->getManager();
            $data = $form->getData();
            $user = $doctrine->getRepository(User::class)->findBy(array('email'=>$data['email']));
            $message = (new \Swift_Message('hello Email'))
                ->setSubject('Confirmation changement de mot de passe')
                ->setFrom('dem0onn70290@gmail.com')
                ->setTo($data['email'])
                ->setContentType('text/html')
                ->setBody('Information, votre mot de passe à été changé');
            $this->get('mailer')->send($message);
            $password = $encoder->encodePassword($user[0],$data['password']);
            $user[0]->setPassword($password);
            $em->persist($user[0]);
            $em->flush();
            return $this->redirectToRoute('index.index');
        }
        return new Response($twig->render('security/formPassword.html.twig',['form'=>$form->createView()]));
    }
}