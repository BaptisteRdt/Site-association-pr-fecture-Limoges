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
    private function registerVisit(EntityManagerInterface $entityManager)
    {
        $viewLog = new ViewLog();
        $viewLog->setDate(new \DateTime("now", new \DateTimeZone("Europe/Paris")));
        $entityManager->persist($viewLog);
        $entityManager->flush();
    }

    #[Route('/panier', name: 'panier')]
    public function index(EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();

        $this->registerVisit($em);
        $entity = $em->getRepository(News::class)->findBy(array(), array('id' => 'DESC'),5 ,0);

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
