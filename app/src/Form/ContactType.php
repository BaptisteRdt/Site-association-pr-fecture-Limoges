<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints'=> [
                    new NotBlank(['message'=>'Entrer le nom'])
                ],
                "label"=>'Nom'
            ])
            ->add('firstname', TextType::class, [
                'constraints'=> [
                    new NotBlank(['message'=>'Entrer le prénom'])
                ],
                "label"=>'Prénom'
            ])
            ->add('mailaddress', EmailType::class, [
                'constraints'=> [
                  new NotBlank(['message'=>'Entrer le mail'])
                ],
                "label"=>'Adresse mail'
            ])
            ->add('subject', TextType::class, [
                'constraints'=> [
                    new NotBlank(['message'=>'Objet'])
                ],
                "label"=>'Objet'
            ])
            ->add('message', TextAreaType::class, [
                "label"=>'Message'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
