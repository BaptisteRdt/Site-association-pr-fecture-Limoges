<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;


class PanierController extends AbstractController
{
    #[Route('/panier', name: 'panier')]
    public function index(EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();

        $sql = '
            SELECT a.name, r.quantity, r.price FROM reservation r, article a
            WHERE r.article_id = a.id and r.user_id = ' . $this->getUser()->getId() . '  
            ';
        $stmt = $conn->query($sql)->fetchAllAssociative();

        return $this->render('panier/index.html.twig', [
            'controller_name' => 'PanierController',
            'reservations'=> $stmt,
        ]);
    }
}
