<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use App\Form\ArticleFormType;
use App\Form\CategoryFormType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
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

            //On stock en session un message utilisateur contenant l'id de l'article modifié
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
     * @Route("/admin/category/{id}/remove", name="admin_remove_category")
     */
    public function adminCategory(EntityManagerInterface $manager, CategoryRepository $repoCategory, Category $category = null): Response
    {

        $colonnes = $manager->getClassMetadata(Category::class)->getFieldNames();

        //Si la variable $category retourne TRUE, cela veut dire qu'elle contient une categorie de la BDD, alors on entre dans le IF et on tente d'executer la suppression
        if($category)
        {
            
            // Nous avons une relation entre la table Article et Category et une contrainte d'intégrité en RESTRICT, donc nous ne pouvons pas supprimer la cétégorie si 1 article lui est tjs associé
            // getAticles() de l'entité Category retourne tout les articlez associés à la catégorie (relation bi-directionnelle)
            // Si getArticle() retourne un résultat vide, cela veut dire qu'il n'y a plus aucun article associé à la catégorie, nous pouvons donc la supprimer
            if($category->getArticles()->isEmpty())
            {
                $manager->remove($category);
                $manager->flush();

                $this->addFlash('success', "La catégorie a bien été supprimé ");

            }
            else// Sinon dans tout les autres cas, des articles sont toujours associés à la catégorie, on affiche un message erreur utilisateur
            {
                $this->addFlash('danger', "Impossible de supprimer la catégorie => des articles lui sont associés");

            }   

            return $this->redirectToRoute('admin_category');
        }

        $category = $repoCategory->findAll();

        //dump($category);

        

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
    public function adminFormCategory( Request $request, EntityManagerInterface $manager, Category $category = null ) : Response
    {

        /*
                1. Créer une classe permettant de générer un formulaire correspondant à l'entité Catégory(make:form)
                2. dans le controller, faites en sorte d'importer et de créer le formulaire, en le reliant à l'entité
                3. Envoyé le formulaire sur le template (render et l'afficher en front)
                4. Récupérer et envoyer les données de $_POST dans la bonne entité à la validation du formulaire (handleRequest + $request)
                5. Générer et executer la requete d'insertion à la validation du formulaire ($manager + persist + flush)
        */ 
        // Si l'objet entité $catégory n'existe pas, et donc 'NULL', ça veut dire qu'on est sur la route /admin/category/new, que nous souhaitons créer une nouvelle catégorie, alors on entre ds le IF
        //si l'objet entité $category possède un id, cela veut dire que nous sommes sur la route "admin/category/{id}/edit, l'id envoyé dans l'URL a été selctionné en BDD, nous souhaitons modifier la catégorie existante
        if(!$category)
        {
            $category = new Category;
        }
        

        // crée moi un formulaire 'CategoryFormType'qui remplisse mon entité Category
        $formCategory = $this->createForm(CategoryFormType::class, $category,[
            'validation_groups' => ['category']
        ]); // 
                   

        dump($request);

        $formCategory->handleRequest($request);//$_POST ['title']-->envoi dans-->setTitle($_POST['title']) 

        dump($category);

        if($formCategory->isSubmitted() && $formCategory->isValid())
        {
            if(!$category->getId())
                $message = "La catégorie" . $category->getTitle() . "a bien été enregistré";
            else 
                $message = "La catégorie" .$category->getTitle() . "a été modifié avec succès";

            $manager->persist($category);// on prépare et on garde en mémoire la requête INSERT
            $manager->flush();

            $this->addFlash('success', "La catégorie a bien été enregistré ");

            return $this->redirectToRoute('admin_category');
        }
       

        return $this->render('admin/admin_form_category.html.twig', [
            'formCategory' => $formCategory->createView()// creatview() pr avoir un  objet manipulable en Twig

        ]);


    }

    /**
     * Méthode permettant d'afficher tout les commentaires des articles stockés en BDD
     * Méthode permettant de supprimer un commentaire en BDD
     * 
     * @Route("/admin/comments", name="admin_comments")
     * @Route("/admin/comment/{id}/remove", name="admin_remove_comment")
     */

    public function adminComment(EntityManagerInterface $manager, CommentRepository $repoComment, Comment $comment = null): Response
    {
        /**
        *1. Faites en sorte de récupérer les métadonnées de la table Comment afin de récupérer le nom des champs/colonne de la table SQL comment et les transmettre au template
        * 2. Afficher le nom des champs/colonne sous forme de tableau HTML
        * 3. Dans le controller, sélctionner tout les commentaires stockés en BDD et les transmettre au template
        * 4. Afficher tout les commentaires de la BDD sous forme de tableau HTML dansle template
        * 5. Prévoir 2 liens (modification / suppression) pour chaque commentaire 
        * 6. Réaliser le traitement permettant de supprimer un commentaire dans la BDD
        *  
        * 
        */
        
        $colonnes = $manager->getClassMetadata(Comment::class)->getFieldNames();

        dump($colonnes);

        $commentBdd = $repoComment->findAll();

        dump($comment);


        return $this->render('admin/admin_comments.html.twig',[
                'colonnes' => $colonnes,
                'commentBdd' => $commentBdd


        ]);
    }

    /**
     * Méthode permettant de modifier un commentaire en BDD
     * 
     * 
     * @Route("/admin/comment/{id}/edit", name="admin_edit_comment")
     */
    public function editComment(): Response
    {

        return $this->render('admin/admin_edit_comments.html.twig');
    }




}
