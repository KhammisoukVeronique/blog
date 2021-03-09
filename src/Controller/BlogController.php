<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function index(): Response
    {
        return $this->render('blog/index.html.twig', [
            'title' => 'Listes des Articles',
        ]);
    }

    /** 
     * Méthode permettant d'afficher le détail d'un article
     *
     * @Route("/blog/12", name="blog_show")
    */
    public function show(): Response
    {
        return $this->render('blog/show.html.twig');

    }

}
