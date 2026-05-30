<?php

namespace App\Form;

use App\Entity\Application;
use App\Entity\CandidateProfile;
use App\Entity\JobOffer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('statut')
            ->add('dateCandidature', null, [
                'widget' => 'single_text',
            ])
            ->add('messageMotivation')
            ->add('candidateProfile', EntityType::class, [
                'class' => CandidateProfile::class,
                'choice_label' => 'id',
            ])
            ->add('jobOffer', EntityType::class, [
                'class' => JobOffer::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Application::class,
        ]);
    }
}
