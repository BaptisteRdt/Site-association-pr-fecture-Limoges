<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Route('/admin/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get("image")->getData();

            if ($image != null){
                $name = md5(uniqid()).'.'.$image->guessExtension();
                $image->move('ImageProfil', $name);

                $user->setImageName($name);
            }

            $user->setCart(array());
            $password = $form->getData()->getPassword();
            $form->getData()->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->getData()->getPassword()
                )
            );

            $email = (new TemplatedEmail())
                ->from('support@asso-prefecture.com')
                ->to($user->getMail())
                ->subject('Inscription sur le site de l\'association de la préfecture de Haute Vienne')

                ->htmlTemplate('mailer/MailNewUser.html.twig')
                ->context([
                    'firstname' => $user->getFirstName(),
                    'lastname' => $user->getLastName(),
                    'username' => $user->getUsername(),
                    'password' => $password,
                ]);

            $mailer->send($email);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->remove('password')
            ->add('plainPassword', PasswordType::class,[
                'label' => 'Nouveau mot de passe',
                'required' => false,
                ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get("image")->getData();

            if ($image != null){
                $name = md5(uniqid()).'.'.$image->guessExtension();
                $image->move('ImageProfil', $name);

                $user->setImageName($name);
            }



            $user = $form->getData();
            if ($user->getPlainPassword() !== null) {
                    $user->setPassword($passwordHasher->hashPassword(
                        $user,
                        $user->getPlainPassword(),
                    )
                );
            }

            $entityManager->flush();
            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    

    #[Route('/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {

            $email = (new TemplatedEmail())
                ->from('support@asso-prefecture.com')
                ->to($user->getMail())
                ->subject('Suppression de votre compte sur le site de l\'association de la préfecture de Haute Vienne')

                ->htmlTemplate('mailer/MailDeleteUser.html.twig')
                ->context([
                    'firstname' => $user->getFirstName(),
                    'lastname' => $user->getLastName(),
                    'username' => $user->getUsername(),
                ]);

            $mailer->send($email);


            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
    }
}
