<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder

            /*
             |--------------------------------------------------------------------------
             | EMAIL
             |--------------------------------------------------------------------------
             */
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
            ])

            /*
             |--------------------------------------------------------------------------
             | NOM
             |--------------------------------------------------------------------------
             */
            ->add('nom', TextType::class, [
                'label' => 'Nom',
            ])

            /*
             |--------------------------------------------------------------------------
             | PRENOM
             |--------------------------------------------------------------------------
             */
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
            ])

            /*
             |--------------------------------------------------------------------------
             | CHOIX DU RÔLE
             |--------------------------------------------------------------------------
             */
            ->add('roleType', ChoiceType::class, [
                'mapped' => false,
                'label' => 'Je souhaite utiliser SkillLink en tant que',
                'choices' => [
                    'Candidat' => 'candidate',
                    'Recruteur / Entreprise' => 'recruiter',
                ],
                'placeholder' => 'Sélectionnez votre profil',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner un type de compte.',
                    ]),
                ],
            ])

            /*
             |--------------------------------------------------------------------------
             | CGU + POLITIQUE CONFIDENTIALITÉ
             |--------------------------------------------------------------------------
             */
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'J’accepte les CGU et la politique de confidentialité',
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions générales et la politique de confidentialité.',
                    ]),
                ],
            ])

            /*
             |--------------------------------------------------------------------------
             | CONSENTEMENT RGPD
             |--------------------------------------------------------------------------
             */
            ->add('agreePrivacy', CheckboxType::class, [
                'mapped' => false,
                'label' => 'Je consens au traitement de mes données personnelles (RGPD)',
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez consentir au traitement de vos données personnelles.',
                    ]),
                ],
            ])

            /*
             |--------------------------------------------------------------------------
             | PASSWORD
             |--------------------------------------------------------------------------
             */
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Mot de passe',
                'attr' => [
                    'autocomplete' => 'new-password',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
                'help' => 'Utilisez un mot de passe fort pour protéger votre compte.',
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