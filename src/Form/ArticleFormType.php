<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            // On ajoute le champ 'category' au formulaire d'insertion des articles étant donné que nous sommes obligé, avec les relations entre les tables, d'associer un article à une catégorie
            // Il faut préciser de quelle entité provient ce champs (Category)
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label'=> 'title'

            ])
            ->add('content')
            ->add('image')
            //->add('createdAt') nous n'avons pas de champ 'date' dans formulaire
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
