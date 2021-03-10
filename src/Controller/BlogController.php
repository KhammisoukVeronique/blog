<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
            'title' => "Bienvenue sur le blog Symfony",
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

        $articles = $repo->findAll();// equivalent de SELECT * FROM article + fetchAll
        dump($articles);

        return $this->render('blog/index.html.twig', [
            'title' => 'Less is more !',
            'articles' => $articles // on envoie sur le template, les articles selectionnés en BDD afin de pouvoir les afficher dynamiquement sur le template à l'aide du langage Twig
        ]);
    }


    /**
     * Méthode permettant d'insérer et de modifier un article
     * @Route("/blog/new", name="blog_create")
     */
    public function create(Request $request): Response
    {
        // la classe Request de Symfony permet de véhiculer les données des superglobales PHP ($_POST, $_FILES, $_COOKIE, $_SESSION)
        // $request est un objet issu de la class Request injecté en dépendance de la méthode create()
        dump($request);
        
        return $this->render('blog/create.html.twig');

    }

    /** 
     * Méthode permettant d'afficher le détail d'un article
     * On définit ici une route paramétrée, une route définit avec un ID qui va réceptionner un ID d'un article dans l'URL
     * /blog/9 --> {id}--> $id = 9
     * 
     * @Route("/blog/{id}", name="blog_show")
    */
    public function show(Article $article): Response
    {
        // $repoArticle est un objet de la class ArticleRepository
        //$repoArticle = $this->getDoctrine()->getRepository(Article::class);

        //dump($repoArticle);

        //dump($id); //$id = 9

        // On transmet à la méthode find() de la class ArticleRepository l'id récupéré dans l'URL et transmit en argument de la fonction show() | $id = 9
        //$article = $repoArticle->find($id);

        dump($article);

        return $this->render('blog/show.html.twig', [
            'articleTwig' => $article //// on envoi sur le template les données selectionnées en BDD, c'est à dire les informations d'1 article en fonction l'id transmit dans l'URL
        ]);
            

    }

    /*
        En fonction de la route paramétrée {id} et de l'injection de dépendance $article, Sympfony voit que l'on a besoin d'un article de la BDD par rapport à l'ID transmit dans l'URL, il est donc capable de récupérer l'ID et de selectionner en BDD l'article correspondant et de l'envoyer directement en argument de la méthode show(Article $article)
        Tout ça  grâce à des ParamConverter qui appel des convertisseurs pour convertir les paramètres de l'objet. Ces objets sont stockés en tant qu'attribut de requête et peuvent donc être injectés an tant qu'argument de méthode de controller

    */
    
    

}
