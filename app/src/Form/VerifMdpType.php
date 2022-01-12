<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\EqualTo;

class VerifMdpType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options["data"][0];
        $passwordHasher = $options["data"][1];
        $builder
            ->add('plainPassword', PasswordType::class, [
                'constraints' => New EqualTo([
                    'value'=> $user->getPassword(),
                    'message'=>'Le Mot de passe n\'est pas valide',
                ]),
                'label'=>'Entrez votre Mot de passe',
                'data' => ''
            ])
            ->add('mail', RepeatedType::class, [
                'type' => EmailType::class,
                'invalid_message' => 'Les deux champs sont différents',
                'options' => ['attr' => ['class' => 'password-field']],
                'first_options'  => [
                    'label' => 'Nouveau Mail',
                    'data'=>''
                ],
                'second_options' => [
                    'label' => 'Confirmer Mail',
                    'data'=>''
                ],
            ])
        -> addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) use ($user, $passwordHasher) {
           $password = $event->getData()['plainPassword'];
           $mail = $event->getData()['mail'];
           if( $passwordHasher->isPasswordValid($user, $password))
           $event->setData(["mail" => $mail, "plainPassword" => $user->getPassword()]);
        })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}