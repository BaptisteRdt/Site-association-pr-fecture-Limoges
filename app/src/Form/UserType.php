<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
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
            ->add('telephone', TextType::class,[
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrer le téléphone']),
                ],
                'label' => 'Téléphone'
            ])
            ->add('address', TextType::class,[
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrer votre adresse postale']),
                ],
                'label' => 'Adresse Postale'
            ])

            ->add('birthDate', TextType::class,[
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrer la date de naissance']),
                ],
                'label' => 'Date de Naissance'
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN'
                ],
                'expanded' => true,
                'multiple' => true,
                'label' => 'Rôles'
            ])
            ->add('image', FileType::class, [
                'label' => 'Image de profil',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2m',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Choisissez un fichier valide',
                    ])
                ],
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
