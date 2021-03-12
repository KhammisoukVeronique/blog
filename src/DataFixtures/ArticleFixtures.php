<?php

namespace App\DataFixtures;
use DateTime;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;


class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // J'importe la librairie Faker installée via composer
       $faker = \Faker\Factory::create('fr_FR');
       // Librairie permettant de créer des données fictives (noms, adresse, phrase, date etc...)


       //Création de 3 catégories
        for($i = 1; $i <= 3; $i++)
       {    
           //Pour insérer dans la table Category, nous devons remplir des objets issu de son Entity Category::class
            $category = new Category;

            //On appel les setteurs de l'objet
            $category->setTitle($faker->sentence()) // création de phrases aléatoirs
                     ->setDescription($faker->paragraph());// on crée un paragraphe aléatoire

            $manager->persist($category);// On garde en mémoire et on prépare les requêtes d'insertion

            // Création de 4 à 6 articles
            for($j = 1; $j <= mt_rand(4,6); $j++)
            {
                //Pr insérer dans la table Article, nous devons remplir des objets issu de son entité Article::class
                $article = new Article;

                // On crée 5 paragraphes que l'on rassemble en une chaine de caractères (join)
                $content = '<p>' . join($faker->paragraphs(5), '</p><p>') . '</p>'; 

                $article->setTitle($faker->sentence())// phrases aléatoire pr le titre
                        ->setContent($content)// paragraphe aléatoire pr le contenu
                        ->setImage("https://picsum.photos/seed/picsum/600/400") // image aléatoire
                        ->setCreatedAt($faker->dateTimeBetween('-6 months')) //on crée des dates aléatoires d'article de  moins de 6 mois à aujourd'hui
                        ->setCategory($category);// on relie les articles aux catégories (clé étrangère)
                
                $manager->persist($article);        

                // Création de 4 à 10 commentaires pr chaque article
                for($k = 1; $k <= mt_rand(4,10); $k++)
                {
                    $comment = new Comment;

                    $content = '<p>' . join($faker->paragraphs(2), '</p><p>') . '</p>';

                    $now = new \DateTime();// retourne la date du jour
                    $interval = $now->diff($article->getCreatedAt());// retourne un timestamp (temps en seconde) entre la date de création des articles et aujourd'hui
                    $days = $interval->days;// nombre de jour entre la date de création des articles et aujourd'hui
                    $minimun = "-$days days"; /* -100 days le but est d'avoir des commentaires qui à l'interval de la création des articles, des commentaires de - de 6 mois à aujourd'hui */ 


                    $comment->setAuthor($faker->name)
                            ->setContent($content)
                            ->setCreatedAt($faker->dateTimeBetween($minimun))// date aléatoire pr les commentaires, entre la date de création des articles et aujourd'hui
                            ->setArticle($article);// on relie les commantaire aux articles (clé étangère) on transmet les objets aux articles
                
                    $manager->persist($comment);// On garde en mémoire et on prépare les requêtes d'insertion

                }

            }


       }

       $manager->flush();

    }
}
