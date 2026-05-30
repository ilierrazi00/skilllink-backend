<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileSettingsFormType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {

        $builder

            /*
             |--------------------------------------------------------------------------
             | Prénom
             |--------------------------------------------------------------------------
             */
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Votre prénom',
                    'class' => 'form-control',
                ],
            ])

            /*
             |--------------------------------------------------------------------------
             | Nom
             |--------------------------------------------------------------------------
             */
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Votre nom',
                    'class' => 'form-control',
                ],
            ])

            /*
             |--------------------------------------------------------------------------
             | Email
             |--------------------------------------------------------------------------
             */
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Votre adresse email',
                    'class' => 'form-control',
                ],
            ])

            /*
             |--------------------------------------------------------------------------
             | Statut utilisateur
             |--------------------------------------------------------------------------
             */
            ->add('statut', TextType::class, [
                'label' => 'Statut',
                'required' => false,
                'disabled' => true,
                'attr' => [
                    'class' => 'form-control bg-slate-200 cursor-not-allowed',
                ],
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {

        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}