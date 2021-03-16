<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleFormType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * Méthode permettant d'afficher l'accueil du BackOffice
     * 
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * Méthode permettant d'afficher toute la liste des articles sous forme de tableau HTML dans le BO
     * La deuxième route permet de supprimer un article dans la BDD en fonction de son ID
     * 
     * @Route("/admin/articles", name="admin_articles")
     * @Route("/admin/{id}/remove", name="admin_remove_article")
     * 
     */
    public function adminArticles(EntityManagerInterface $manager, ArticleRepository $repoArticle, Article $article = null): Response
    {
        // Le manager permet de manipuler la BDD, on execute la méthode getClassMetadata() afin de selectionner les méta données des colonnes (primary key, not nul, type, taille etc...)
        //getFieldsNames() permet de selectionner le nom des chmaps/colonne de la table Article de la BDD
        // $colonnes = $data->getColumnMeta()->colonne['name'] -- DESC article
        $colonnes = $manager->getClassMetadata(Article::class)->getFieldNames();

        dump($article);

        // Selection de tout les articles en BDD
        $articles = $repoArticle->findAll(); // SELECT * FROM article + FETCHALL

        dump($articles);

        // Si la conditon IF retourne TRUE, cela veut dire que la variable $article contient bien l'article à supprimer de la BDD, on entre dans le IF
        if($article)
        {
            //avant la suppression, on stock l'id de l'article a supprimer dans une variable afin de l'injecter dans le message de validation
            $id = $article->getId();

            $manager->remove($article);// on prépare la requête de suppression (DELETE), on la garde en mémoire
            $manager->flush();// on execute la requête de suppression

            $this->addFlash('success', "L'article n°$id a bien été supprimé ");

            return $this->redirectToRoute('admin_articles');

        }

        return $this->render('admin/admin_articles.html.twig', [
                'colonnes'=> $colonnes, // on transmet à la méthode render le nom des champs/colonnes selectionnés en BDD afin de pouvoir les réceptionner sur le template et de pouvoir des afficher 
                'articlesBdd'=> $articles // on transmet à la méthode render les articles selectionnés en BDD au 
        ]);

    }

    /**
     * Méthode permettant de modifier un article existant dans le BO
     * 
     * @Route("/admin/{id}/edit-article", name="admin_edit_article")
     */
    public function adminEditArticle(Article $article, Request $request, EntityManagerInterface $manager)
    {
        dump($article);

        // On crée un formulaire via la class ArticleFormType qui a pr but de remplir l'entité $article
        $formArticle = $this->createForm(ArticleFormType::class, $article);

        dump($request);

        $formArticle->handleRequest($request); // $_POST['title'] --> $article->setTitle($_POST['title'])

        if($formArticle->isSubmitted() &&  $formArticle->isValid())
        {
            //On entre dans le IF seulement si le formulaire a bien été validé et que chaque donnée est transmise aux bons setter de l'entité
            
            $manager->persist($article); // on prépare la requete de modification
            $manager->flush();// on execure la requete de modification SQL

            //On stock en session un message utilisatieur contenant l'id de l'article modifié
            $this->addFlash('success', "L'article n°" . $article->getId() ." a bien été modifié");

            // Une fois la modification executé, on redirige l'internaute vers l'affichage des articles dnas le BO
            return $this->redirectToRoute('admin_articles');
        }

        return $this->render('admin/admin_edit_article.html.twig', [
                'idArticle' => $article->getId(),
                'formArticle' => $formArticle->createView()
        ]);

    }

    /**
     * Méthode permettant d'afficher sous forme de tableau HTML les catégories stockées en BDD
     * 
     * @Route("/admin/categories", name="admin_category")
     */
    public function adminCategory(EntityManagerInterface $manager, CategoryRepository $repoCategory, Category $category): Response
    {

        $colonnes = $manager->getClassMetadata(Category::class)->getFieldNames();

        $category = $repoCategory->findAll();

        return $this->render('admin/admin_category.html.twig', [
            'colonnes' => $colonnes,
            'categorieBdd' => $category
        ]);
    }
    /**
     * 
     * @Route("/admin/category/new", name="admin_new_category")
     * @Route("/admin/category/{id}/edit", name="admin_edit_category")
     * 
     */
    public function adminFormCategory()
    {

        return $this->render('admin/admin_form_category.html.twig');
    }







}
