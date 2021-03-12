<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use Monolog\Handler\Handler;
use App\Form\ArticleFormType;
use App\Form\CommentFormType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    /**
     * Méthode qui affiche la page d'acceuil
     * @Route("/", name="home")
     */
    public function home(): Response
    {
        return $this->render('blog/home.html.twig', [ // twig language de rendu
            'title' => "Bienvenue sur le blog AnOther Symfony",
            'age' => 30
        ]);
    }

    /**
     * Méthode permettant d'afficher toute la liste des articles stockés en BDD
     * 
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo): Response
    {
        /**
         *  Pour selectionner des donnés dans une table SQL, nous devons absolument avoir accès à la class Repository de l'entité correspondante
         * Un Repository est une class permettant uniquement d'executer des requêtes de selection en BDD (SELECT)
         * Nous devons donc accéder au repository de l'entité Article au sein de notre controller
         * 
         * On appel l'ORM doctrine (getDoctrine()), puis on importe le repository de la class Article grâce à la méthode getRepository()
         * $repo est un objet issu de la class ArticleRepository, cet objet contient des méthodes permettant d'executer des requêtes de selection
         * finAll() : méthode issue de la classe ArticleRepository permettant de selectionner l'ensemble de la table SQL 'Article'
        */ 
        

        //outil de debugage de Symfony  (équivalent d'un var_dump en PHP)
        dump($repo);

        // Doctrine selectionne en BDD tout les articles et les transmet au controller
        $articles = $repo->findAll();// equivalent de SELECT * FROM article + fetchAll
        dump($articles);

        return $this->render('blog/index.html.twig', [
            'title' => 'Less is more !',
            'articles' => $articles // on envoie sur le template, les articles selectionnés en BDD afin de pouvoir les afficher dynamiquement sur le template à l'aide du langage Twig
        ]);
    }


    /**
     * Méthode permettant d'insérer et de modifier un article
     * il est possible de définir plusiseur route qui execute la même méthode dans le controller
     * 
     * @Route("/blog/new", name="blog_create")
     * @Route("/blog/{id}/edit", name="blog_edit")
     */
    public function create(Article $articleCreate = null, Request $request, EntityManagerInterface $manager): Response
    {
        // la classe Request de Symfony permet de véhiculer les données des superglobales PHP ($_POST, $_FILES, $_COOKIE, $_SESSION)
        // $request est un objet issu de la class Request injecté en dépendance de la méthode create()
        dump($request);
        
        //$request permet de stocker les données des superglobales, la propriété $request->request permet de stocker les données véhiculées par un forulaire ($_POST), ici on compte si il y a des données qui on été saisie dans le formulaire
        // if($request->request->count() > 0)
        // {
            // Pour insérer dans la table Article, nous devons instancier un objet issu de ka classe entité Article, qui est lié à la table SQL Article
            
            // Si la variable $articleCreate N'EST PAS, si elle ne contient aucun article de la BDD, cela veut dire que nous avons envoyé la route '/blog/new', c'est une insertion, on entre dans le IF et on crée une nouvelle instance de l'entité Article, création d'un nouvel article
            // Si la variable $articleCreate contient un article de la BDD, cela veut dire que nous avont envoyé la route '/blog/id/edit', c'est une modifiction d'article, on entre pas dans le IF
            if(!$articleCreate)
            {
                $articleCreate = new Article;
            }
           

            //On renseigne tout les setteurs de l'objet avec en argument les données du formulaire ($_POST)
            // $articleCreate ->setTitle($request->request->get('title')) //$_POST['tilte']
            //                 ->setContent($request->request->get('content'))
            //                 ->setImage($request->request->get('image'))
            //                 ->setCreatedAt(new \DateTime);
        
                            //dump($articleCreate);// On observe que l'objet entité Article $articleCreate, les propriétés contiennent bien les données du formaulaire

                            // On fait appel au manager afin de pouvoir executer une insertion en BDD
                            // $manager->persist($articleCreate);// on prépare et garde en mémoire l'insertion
                            // $manager->flush();// on execute l'insertion

                            //Après l'insertion, on redirige l'internaute vers le détail de l'article qui vient d'être inséré en BDD
                            // Cela correspond à la route 'blog_show', mais c'est une route paramétrée qui attend un ID dans l'URL
                            // En 2ème argument de redirectToRoute, nous transmettons l'ID de l'article qui vient d'être inséré en BDD
                            // return $this->redirectToRoute('blog_show', [
                            //     'id' => $articleCreate->getId() // dernier id inséré en BDD
                            // ]);

        //}
        
        
        // creatFormBuilder() méthode de Symfony qui permet de générer un formulaire permettant de remplir une entité $articleCreate
        // $form = $this->createFormBuilder($articleCreate)
        //              ->add('title') // add() méthode qui permet de génrérer des champs de formulaire

        //              ->add('content')

        //              ->add('image')

        //              ->getForm();// permet de valider le formulaire

        //$articleCreate = new Article;
        
        // Ici nous renseignons le setter de l'objet et Symfony est capable automatiquement d'envoyer les valeurs de l'entité directement dans les attributs 'value' du formulaire, étant donné que l'entité $articleCreate est relié au formulaire
        // $articleCreate->setTitle("Titre dump")
        //               ->setContent("Contenu dump");
        
                      //Nous avons crée une class qui permet de générer le formulaire d'ajouter d'article, il faut dans le controller importer cette class ArticleFormType et relier le formulaire à notre entité Article $articleCreate
        $form = $this->createForm(ArticleFormType::class, $articleCreate);
 
        

        // On pioche dans l'objet du formulaire la méthode handleRequest() qui permet de récupérer chaque données saisie dans le formulaire ($request) et de les binder, de les transmettre directement dans les bon setteurs de mon entité
        
        //  $_POST['title'] --> setTitle($_POST['title'])            
        $form->handleRequest($request);

        //dump($articleCreate);

        //Si le formulaire a bien été soumit et que chauque valeur du formulaire ont bien  été transmises dans les bons setter de l'entité (isValid()), alors on entre dans le IF et on génère l'insertion
        if($form->isSubmitted() && $form->isValid())
        {
            // On entre dans le IF seulement dans le cas où le formulaire a été validé et que chaque donnée st bien envoyé dans les bons setter de l'entité
            // On appel le seter de la date, puisque nous n'avons pas de champs date dans le formulaire

            // On entre dans le IF seulement dans le cas d'une insertion, et que l'article ne possède pas d'ID
            if(!$articleCreate->getId())
            {
                $articleCreate->setCreatedAt(new \DateTime);
            }
            

            $manager->persist($articleCreate); // on appel le manager pr préparer la requête d'insertion et la garder en mémoire
            $manager->flush();// On exécute la requête d'insertion en BDD

            return $this->redirectToRoute('blog_show', [
                    'id' => $articleCreate->getId()
            ]);

        } 

        return $this->render('blog/create.html.twig', [
                'formArticle' => $form->createView(),// On transmet sur le template le formulaire, creatView() retourne un petit objet qui représente l'affichage du formulaire, on récupère sur le template create.html.twig

                'editMode' => $articleCreate->getId() // cela permettra dans le template de savoir si l'article possède un ID ou non, si c'est une insertion ou une modification
        ]);

    }

    /** 
     * Méthode permettant d'afficher le détail d'un article
     * On définit ici une route paramétrée, une route définit avec un ID qui va réceptionner un ID d'un article dans l'URL
     * /blog/9 --> {id}--> $id = 9
     * 
     * @Route("/blog/{id}", name="blog_show")
    */
    public function show(Article $article, Request $request, EntityManagerInterface $manager): Response
    {
        //dump($article);
        $comment = new Comment;

        $formComment = $this->createForm(CommentFormType::class, $comment);

        dump($request);

        $formComment->handleRequest($request);
     

        if($formComment->isSubmitted() && $formComment->isValid())
        {
            // On entre dans cette condition uniquement dans le cas où nous avons validé le formulaire et que chque valeur saisie ont bien été transmises au bon setter de l'objet

            $comment->setCreatedAt(new \DateTime)
                    ->setArticle($article); // on relie le commentaire à l'article
                    //la  méthode setArticle() attend en argument un objet issu de la class Article, c'est à dire un article de la BDD
                    dump($comment);
            $manager->persist($comment);
            $manager->flush();

            // Envoi d'un message de validation en session grâce à la méthode addFlash()
            //1. success : identifiant du message
            //2. le message
            $this->addFlash('success', "Le commentaire a bien été posté");

            return $this->redirectToRoute('blog_show', [
                'id'=> $article->getId()
            ]);
        }
        

        


        
       
        return $this->render('blog/show.html.twig', [
            'articleTwig' => $article, //// on envoi sur le template les données selectionnées en BDD, c'est à dire les informations d'1 article en fonction l'id transmit dans l'URL
            'formComment' => $formComment->createView()
        ]);
            

    }

    /*
        En fonction de la route paramétrée {id} et de l'injection de dépendance $article, Sympfony voit que l'on a besoin d'un article de la BDD par rapport à l'ID transmit dans l'URL, il est donc capable de récupérer l'ID et de selectionner en BDD l'article correspondant et de l'envoyer directement en argument de la méthode show(Article $article)
        Tout ça  grâce à des ParamConverter qui appel des convertisseurs pour convertir les paramètres de l'objet. Ces objets sont stockés en tant qu'attribut de requête et peuvent donc être injectés an tant qu'argument de méthode de controller

    */
    
    

}
