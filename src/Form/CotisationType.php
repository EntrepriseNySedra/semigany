<?php

namespace App\Form;

use App\Entity\Cotisation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;


class CotisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('montant', NumberType::class, [
                'label' => 'Montant de la cotisation (Ar)',
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('mode_paiement', ChoiceType::class, [
                'label' => 'Mode de paiement',
                'choices' => [
                    'Espece' => 'Espece',
                    'Chèque' => 'Chèque',
                ],
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('reference_paiement', TextType::class, [
                'label' => 'Référence du paiement',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('datePaiement', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de paiement',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cotisation::class,
        ]);
    }
}