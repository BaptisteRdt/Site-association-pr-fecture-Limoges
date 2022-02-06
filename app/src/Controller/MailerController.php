<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;

class MailerController extends AbstractController
{
    #[Route('/mailer', name: 'mailer')]
    public function index(MailerInterface $mailer): Response
    {
        $email = (new TemplatedEmail())
        ->from('support@asso-prefecture.com')
        ->to('antoine2496@gmail.com')
        ->subject('Inscription sur le site')

        ->htmlTemplate('mailer/index.html.twig');

        $mailer->send($email);


        return $this->render('mailer/index.html.twig', [
            'controller_name' => 'MailerController',
        ]);
    }
}
