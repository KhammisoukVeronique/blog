<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     * 
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder): Response
    {

        dump($request);
        // On crée un nouvel exemplaire de l'entité 'User' afin de pouvoir remplir l'objet via le formulaire et l'insérer en BDD
        $user = new User;

        //On execute la méthode creatForm() du SecurityController afin de créer un formulaire par rapport à la class RegistrationFormType destiné à remplir les setters de l'objet entité $user 
        $formRegistration = $this->createForm(RegistrationFormType::class, $user);

        $formRegistration->handleRequest($request);// handleRequest($request) envoie les données du formulaire dans les bons setters de l'entity 'User' c'est une méthode Symfony qui permet à la validation du formulaire
        

        dump($user);

        if($formRegistration->isSubmitted() && $formRegistration->isValid())
        {
            // Si le formulaire a bien été validé (isSubmitted) et que chaque donnée saisie ont bien été transmise aux bons setter de l'objet (isValid), alors on entre dans le IF

            //On encode le mot de passe
            // $hash contient une clé de hachage du mot de passe
            $hash = $encoder->encodePassword($user, $user->getPassword());

            dump($hash);

            // On affecte le mot de passe haché directement à l'entité, au setter de l'objet
            $user->setPassword($hash);
            
            dump($user);

            $manager->persist($user);// préparation et mise en mémoire de la requête INSERT SQL
            $manager->flush(); // execution de la requête SQL

            // On stock un message de validation en session
            $this->addFlash('success', "Bravo ! Votre compte a bien été crée ! Vous pouvez dès à présent vous connecter ");

            // Après l'insertion de l'utilisateur en BDD, on redirige vers le template de connexion
            return $this->redirectToRoute('security_login');

        }
      

        return $this->render('security/registration.html.twig', [
                    'formRegistration' => $formRegistration->createView()// on envoie le formulaire sur le template afin de pouvoir l'afficher en front

        ]);
    }
    
    /**
     * Méthode permettant de se connecter au blog
     * AuthenticationUtils permet de récuperer le dernier e-mail saisie au moment de la connexion
     * AuthenticationUtils permet de récuperer les messages d'erreurs enn cas de mauvaise connexion
     * 
     * @Route("/connexion", name="security_login")
     * 
     */

    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        //Récupération du msg d'erreur en cas de mauvaise connexion
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupération du dernier username (email) saisi par l'internaute dans le formulaire de connexion en cas d'erreur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
                        'error' => $error,
                        'lastUsername' => $lastUsername

        ]);


    }

    /**
     * Méthode permettant de se déconnecter, pas de réponse (return), nous avons juste besoin de la route
     * 
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout()
    {
        // Cette méthode ne retourne rien, il nous suffit d'avoir une route pr se déconnecter
    }

}
