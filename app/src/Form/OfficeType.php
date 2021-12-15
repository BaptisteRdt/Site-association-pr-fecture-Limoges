<?php

namespace App\Form;

use App\Entity\Office;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class OfficeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
        ->add('role', TextType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Entrer votre poste']),
            ],
        ])
        ->add('description', TextAreaType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Entrer votre description']),
            ],
        ])
        ->add('image', FileType::class, [
            'label' => 'Image article',
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
            'data_class' => Office::class,
        ]);
    }
}
