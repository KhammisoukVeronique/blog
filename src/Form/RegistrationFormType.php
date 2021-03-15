<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', TextType::class,[
                    'required' => false // on annule la sécurité de base HTML, c'est à dire l'attribut 'required' des balises html imposé par Symfony
            ])
            ->add('username', TextType::class,[
                'required' => false
        ])// PasswordType : class de Symfony permettant d'affecter un type 
            ->add('password', PasswordType::class,[ // PasswordType le mot de passe est caché à l'inscription
                'required' => false
        ])
            ->add('confirm_password', PasswordType::class,[
                'required' => false
        ]) // on ajoute au formulaire un champ de 'confirm_password' qui ne sera pas enregistré en BDD, mais juste pr comparer les mots de pass 
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
