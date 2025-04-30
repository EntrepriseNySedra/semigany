<?php

// src/Form/EventType.php

namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de l\'événement',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
            ])
            ->add('nb_places', NumberType::class, [
                'label' => 'Nombre de places',
            ])
            ->add('tarif', NumberType::class, [
                'label' => 'Tarif',
            ])
            ->add('visible_pour', TextType::class, [
                'label' => 'Visibilité pour',
                'required' => false,
            ])
            ->add('date_limite', DateType::class, [
                'label' => 'Date limite d\'inscription',
                'widget' => 'single_text',
            ])
            ->add('is_ouvert', CheckboxType::class, [
                'label' => 'Ouvert aux inscriptions',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer l\'événement',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
