<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DocumentPageController extends AbstractController
{
    #[Route('/documentPage', name: 'document_page')]
    public function index(): Response
    {
        return $this->render('document_page/index.html.twig', [
            'controller_name' => 'DocumentPageController',
        ]);
    }
}
