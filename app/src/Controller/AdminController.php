<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Article;
use App\Entity\Reservation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin')]
    public function index(EntityManagerInterface $em): Response
    {

        $repoArticles = $em->getRepository(Article::class);
        
        $totalArticle = $repoArticles->createQueryBuilder('a')
            ->select('sum(a.quantite)')
            ->getQuery()
            ->getSingleScalarResult();        

        $repoReservations = $em->getRepository(Reservation::class);
        
        $totalReservation = $repoReservations->createQueryBuilder('a')
            ->select('sum(a.quantity)')
            ->getQuery()
            ->getSingleScalarResult(); 

        return $this->render('admin/index.html.twig', [
            'reservation_percentage' => $totalArticle == 0 ? 0 : 100 * $totalReservation / $totalArticle
        ]);
    }

    #[Route('/admin/panier/{id}', name: 'show_cart')]
    public function showCart(User $user , EntityManagerInterface $em): Response
    {
        $cart= $user->getCart();
        $articles= $em->getRepository(Article::class)->findBy(array('id' => array_keys($cart)));
        foreach ($articles as $article){
            $article->setQuantite($cart[$article->getId()]);
        }

        $conn = $em->getConnection();

        $sql = '
            SELECT a.name, r.quantity, r.price, r.id FROM reservation r, article a
            WHERE r.article_id = a.id and r.user_id = ' . $user->getId() . '
            ';
        $stmt = $conn->query($sql)->fetchAllAssociative();
        
        return $this->render('admin/show_cart.html.twig', [
            'controller_name' => 'PanierController',
            'reservations'=>$stmt,
            'user'=>$user
        ]);
    }

    #[Route('/admin/panier/confirm/{user}/{id}', name: 'confirm_purchase')]
    public function confirm_purchase($id, User $user, EntityManagerInterface $em){

        $reservations = $user->getCart();

        $reservation = $em->getRepository(Reservation::class)
            ->findOneBy(['id' => +$id]);

        $article = $em->getRepository(Article::class)
            ->findOneBy(['id' => $reservation->getArticle()->getId()]);

        $article->setQuantite($article->getQuantite() - $reservation->getQuantity());

        $em->remove($reservation);
        $em->flush();

        return $this->redirectToRoute('show_cart', array('id'=>$user->getId()), Response::HTTP_SEE_OTHER);
    }


    #[Route('/admin/panier/remove/{user}/{id}', name: 'remove_reservation')]
    public function remove_reservation($id, User $user, EntityManagerInterface $em){

        $reservations = $user->getReservations();

        $reservation = $em->getRepository(Reservation::class)
            ->findOneBy(['id' => +$id]);

        $em->remove($reservation);
        $em->flush();

        return $this->redirectToRoute('show_cart', ['id'=>$user->getId()], Response::HTTP_SEE_OTHER);
    }

}
