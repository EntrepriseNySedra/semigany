<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email')
        ->add('password', PasswordType::class)
        ->add('nom', TextType::class)
        ->add('prenom', TextType::class)
        ->add('telephone', TextType::class)
        ->add('promotion', TextType::class)
        ->add('anneeDebut', TextType::class, ['required' => false])
        ->add('anneeFin', TextType::class, ['required' => false])
        ->add('profession', TextType::class, ['required' => false])
        ->add('adresse', TextareaType::class, [
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
