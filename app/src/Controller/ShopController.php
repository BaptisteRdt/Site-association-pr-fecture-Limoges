<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;

class ShopController extends AbstractController
{
    #[Route('/boutique', name: 'shop')]
    public function index(ArticleRepository $articleRepository): Response
    {

        return $this->render('shop/index.html.twig', [
            'controller_name' => 'ShopController',
            'article' => $articleRepository->findAll(),
        ]);
    }
}
