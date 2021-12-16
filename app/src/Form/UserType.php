<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class,[
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrer le nom d\'utilisateur']),
                ],
                'label' => 'Identifiant'
            ])
            ->add('FirstName', TextType::class,[
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrer le prénom']),
                ],
                'label' => 'Prénom'
            ])
            ->add('LastName', TextType::class,[
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrer le nom']),
                ],
                'label' => 'Nom'
            ])
            ->add('mail', EmailType::class,[
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrer le mail']),
                ],
                'label' => 'Mail'
            ])
            ->add('password', PasswordType::class,[
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrer le mot de passe']),
                ],
                'label' => 'Mot de passe'
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN'
                ],
                'expanded' => true,
                'multiple' => true,
                'label' => 'Rôles'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
