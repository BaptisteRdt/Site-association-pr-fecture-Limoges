<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

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
