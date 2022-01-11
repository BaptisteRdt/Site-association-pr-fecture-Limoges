<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                "label"=>'Nom'
            ])
            ->add('firstname', TextType::class, [
                "label"=>'Prénom'
            ])
            ->add('mailaddress', TextType::class, [
                "label"=>'Adresse mail'
            ])
            ->add('subject', TextType::class, [
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
