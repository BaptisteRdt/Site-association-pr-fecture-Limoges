<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\VerifMdpType;
use App\Form\ProfileType;
use App\Form\ChangeAddressType;
use App\Form\ChangeTelephoneType;
use App\Form\ChangeImageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/profil')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'profile', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'user' => $this->getUser(),
        ]);
    }


    #[Route('/verifMail', name: 'verif_mail', methods: ['GET','POST'])]
    public function verifMail(EntityManagerInterface $em, Request $request, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): Response
    {
        $user = $this->getUser();
        $entity = $em->getRepository(User::class)->findOneBy(['username' => $user->getUserIdentifier()]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userData = $form->getData();
            if ($userData->getPlainPassword() !== null) {
                $userData->setPassword($passwordHasher->hashPassword(
                    $userData,
                    $userData->getPlainPassword(),
                )
                );
            }
            $em->flush();
            return $this->redirectToRoute('profile', [], Response::HTTP_SEE_OTHER);
        }

        $em->refresh($user);

        return $this->render('profile/edit.html.twig', [
            'controller_name' => 'ProfileController',
            'form' => $form->createView(),
        ]);
    }


    #[Route('/changeAddress', name: 'change_address', methods: ['GET','POST'])]
    public function changeAddress(EntityManagerInterface $em, Request $request, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): Response
    {
        $user = $this->getUser();
        $entity = $em->getRepository(User::class)->findOneBy(['username' => $user->getUserIdentifier()]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $form = $this->createForm(ChangeAddressType::class, $user);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();
            return $this->redirectToRoute('profile', [], Response::HTTP_SEE_OTHER);
        }

        $em->refresh($user);

        return $this->render('profile/editAddress.html.twig', [
            'controller_name' => 'ProfileController',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/changeTelephone', name: 'change_telephone', methods: ['GET','POST'])]
    public function changeTelephone(EntityManagerInterface $em, Request $request, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): Response
    {
        $user = $this->getUser();
        $entity = $em->getRepository(User::class)->findOneBy(['username' => $user->getUserIdentifier()]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $form = $this->createForm(ChangeTelephoneType::class, $user);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();
            return $this->redirectToRoute('profile', [], Response::HTTP_SEE_OTHER);
        }

        $em->refresh($user);

        return $this->render('profile/editTelephone.html.twig', [
            'controller_name' => 'ProfileController',
            'form' => $form->createView(),
        ]);
    }


    #[Route('/changeImage', name: 'change_image', methods: ['GET','POST'])]
    public function changeImage(EntityManagerInterface $em, Request $request, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): Response
    {
        $user = $this->getUser();
        $entity = $em->getRepository(User::class)->findOneBy(['username' => $user->getUserIdentifier()]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $form = $this->createForm(ChangeImageType::class, $user);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($user->getImageName() != null){
                $filesystem = new Filesystem();
                $filesystem->remove("ImageProfil/" .$user->getImageName());
            }


            $image = $form->get("image")->getData();

            if ($image != null){
                $name = md5(uniqid()).'.'.$image->guessExtension();
                $image->move('ImageProfil', $name);

                $user->setImageName($name);
            }

            $em->flush();
            return $this->redirectToRoute('profile', [], Response::HTTP_SEE_OTHER);
        }

        $em->refresh($user);

        return $this->render('profile/editImage.html.twig', [
            'controller_name' => 'ProfileController',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/verifMdp', name: 'verif_mdp', methods: ['GET','POST'])]
    public function verifMdp(EntityManagerInterface $em, Request $request, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator, MailerInterface $mailer): Response
    {
        $user = $this->getUser();
        $entity = $em->getRepository(User::class)->findOneBy(['username' => $user->getUserIdentifier()]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $oldMail = $user->getMail();
        $form = $this->createForm(VerifMdpType::class, [$user, $passwordHasher]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userData = $form->getData();
            if ($userData["mail"] !== null) {
                $newMail = $userData["mail"];
                $user->setMail($userData["mail"]);

                $email1 = (new TemplatedEmail())
                    ->from('support@asso-prefecture.com')
                    ->to($newMail)
                    ->subject('Changement d\'adresse-mail de votre comptre sur le site de l\'association de la préfecture de Haute Vienne')

                    ->htmlTemplate('mailer/MailEditMailNewAddress.html.twig')
                    ->context([
                        'firstname' => $user->getFirstname(),
                        'lastname' => $user->getLastname(),
                        'oldMail' => $oldMail,
                    ]);
                $mailer->send($email1);

                $email2 = (new TemplatedEmail())
                    ->from('support@asso-prefecture.com')
                    ->to($oldMail)
                    ->subject('Changement d\'adresse-mail de votre comptre sur le site de l\'association de la préfecture de Haute Vienne')

                    ->htmlTemplate('mailer/MailEditMailOldAddress.html.twig')
                    ->context([
                        'firstname' => $user->getFirstname(),
                        'lastname' => $user->getLastname(),
                        'newMail' =>$newMail,
                    ]);
                $mailer->send($email2);
            }
            $em->flush();
            return $this->redirectToRoute('profile', [], Response::HTTP_SEE_OTHER);
        }

        $em->refresh($user);

        return $this->render('profile/editPassword.html.twig', [
            'controller_name' => 'ProfileController',
            'form' => $form->createView(),
        ]);
    }


    #[Route('/edit', name: 'profile_edit', methods: ['GET','POST'])]
    public function edit(EntityManagerInterface $em, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        $entity = $em->getRepository(User::class)->findOneBy(['username' => $user->getUserIdentifier()]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userData = $form->getData();
            if ($userData->getPlainPassword() !== null) {
                $userData->setPassword($passwordHasher->hashPassword(
                    $userData,
                    $userData->getPlainPassword(),
                )
                );
            }

            $em->flush();

            return $this->redirectToRoute('profile', [], Response::HTTP_SEE_OTHER);
        }

        $em->refresh($user);

        return $this->render('profile/edit.html.twig', [
            'controller_name' => 'ProfileController',
            'form' => $form->createView(),
        ]);
    }
}
