<?php

namespace App\Form;

use App\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'constraints'=> [
                    new NotBlank(['message'=>'Entrer le titre du document'])
                ],
                "label"=>'Titre'
            ])

            ->add('description', TextAreaType::class, [
                'constraints'=> [
                    new NotBlank(['message'=>'Entrer la description du document'])
                ],
                "label"=>'Description du document'
            ])
            ->add('documentName', FileType::class, [
                'label' => 'Insérez un fichier pdf',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/acrobat',
                            'application/nappdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Choisissez un fichier valide',
                    ])
                ],
            ])
            ->add('isPinned', CheckboxType::class,[
                'required'=>false,
                'label' => 'Epingler le document'
            ])

            ->add('isAdherent', CheckboxType::class,[
                'required'=>false,
                'label' => 'Visible uniquement pour les adhérents'
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}
