<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Article;
use App\Entity\Reservation;
use App\Entity\ViewLog;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
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
            
        $repoUsers = $em->getRepository(User::class);

        $totalUser = $repoUsers->createQueryBuilder('a')
            ->select('count(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $totalAdherent = $repoUsers->createQueryBuilder('a')
            ->select('count(a.id)')
            ->where('a.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADHERENT%')
            ->getQuery()
            ->getSingleScalarResult();

        $repoViewLog = $em->getRepository(ViewLog::class);
        
        $monthArray = array("Janvier", "Fevrier", "Mars", "Avril", "Mai", "Juin", "Juillet", "Aout", "Septembre", "Octobre", "Novembre", "Decembre");
        
        $dailyView = array();
        $monthlyView = array();
        $yearlyView = array();
        
        $today = new \DateTime("now");
        $currentYear = $today->format('Y');
        $currentMonth = $today->format('m');
        $currentDay = $today->format('d');

        for ($i = 0; $i < 24; $i++) {
            $hour = $i < 10 ? '0'.$i : $i;
            $dailyView[$i] = $repoViewLog->createQueryBuilder('a')
                ->select('count(a)')
                ->where('a.date LIKE :date')
                ->setParameter('date', "$currentYear-$currentMonth-$currentDay $hour:%")
                ->getQuery()
                ->getSingleScalarResult();
        }

        for ($i = 1; $i <= date('t'); $i++) {
            $day = $i < 10 ? '0'.$i : $i;
            $monthlyView[$i] = $repoViewLog->createQueryBuilder('a')
                ->select('count(a)')
                ->where('a.date LIKE :date')
                ->setParameter('date', "$currentYear-$currentMonth-$day %")
                ->getQuery()
                ->getSingleScalarResult();
        }

        for ($i = 1; $i <= 12; $i++) {
            $month = $i < 10 ? '0'.$i : $i;
            $yearlyView[$monthArray[$i-1]] = $repoViewLog->createQueryBuilder('a')
                ->select('count(a)')
                ->where('a.date LIKE :date')
                ->setParameter('date', "$currentYear-$month-%")
                ->getQuery()
                ->getSingleScalarResult();
        }

        $totalView = $repoViewLog->createQueryBuilder('a')
            ->select('count(a)')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('admin/index.html.twig', [
            'reservation_percentage' => $totalArticle == 0 ? 0 : 100 * $totalReservation / $totalArticle,
            'user_percentage' => $totalUser == 0 ? 0 : 100 * $totalAdherent / $totalUser,
            'daily_view' => $dailyView,
            'monthly_view' => $monthlyView,
            'yearly_view' => $yearlyView,
            'total_view' => $totalView,
            'total_article' => $totalArticle,
            'total_user' => $totalUser,
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
    public function confirm_purchase($id, User $user, EntityManagerInterface $em, MailerInterface $mailer){

        $reservations = $user->getCart();

        $reservation = $em->getRepository(Reservation::class)
            ->findOneBy(['id' => +$id]);

        $article = $em->getRepository(Article::class)
            ->findOneBy(['id' => $reservation->getArticle()->getId()]);

        $article->setQuantite($article->getQuantite() - $reservation->getQuantity());


        $reservationName = $reservation->getArticle()->getName();
        $reservationQuantity = $reservation->getQuantity();
        $reservationPrice = $reservation->getArticle()->getPrix();


        $cart= $reservation->getUser()->getCart();
        $articles= $em->getRepository(Article::class)->findBy(array('id' => array_keys($cart)));
        foreach ($articles as $article){
            $article->setQuantite($cart[$article->getId()]);
        }

        $em->remove($reservation);
        $em->flush();

        $conn = $em->getConnection();

        $sql = '
                SELECT a.name, r.quantity, r.price, r.id FROM reservation r, article a
                WHERE r.article_id = a.id and r.user_id = ' . $reservation->getUser()->getId() . '
                ';
        $stmt = $conn->query($sql)->fetchAllAssociative();

        $email = (new TemplatedEmail())
            ->from('support@asso-prefecture.com')
            ->to($user->getMail())
            ->subject('Confirmation de votre réservation sur le site de l\'association de la préfecture de Haute Vienne')

            ->htmlTemplate('mailer/MailConfirmPurchase.html.twig')
            ->context([
                'firstname' => $user->getFirstName(),
                'lastname' => $user->getLastName(),
                'articleName' => $reservationName,
                'articleQuantity' =>$reservationQuantity,
                'articlePrice' => $reservationPrice,
                'articles' =>$stmt,
            ]);
        $mailer->send($email);


        return $this->redirectToRoute('show_cart', array('id'=>$user->getId()), Response::HTTP_SEE_OTHER);
    }


    #[Route('/admin/panier/remove/{user}/{id}', name: 'remove_reservation')]
    public function remove_reservation($id, User $user, EntityManagerInterface $em, MailerInterface $mailer){

        $reservations = $user->getReservations();

        $reservation = $em->getRepository(Reservation::class)
            ->findOneBy(['id' => +$id]);

        $cart= $reservation->getUser()->getCart();
        $articles= $em->getRepository(Article::class)->findBy(array('id' => array_keys($cart)));
        foreach ($articles as $article){
            $article->setQuantite($cart[$article->getId()]);
        }

        $reservationName = $reservation->getArticle()->getName();
        $reservationQuantity = $reservation->getQuantity();
        $reservationPrice = $reservation->getArticle()->getPrix();



        $em->remove($reservation);
        $em->flush();

        $conn = $em->getConnection();

        $sql = '
                SELECT a.name, r.quantity, r.price, r.id FROM reservation r, article a
                WHERE r.article_id = a.id and r.user_id = ' . $reservation->getUser()->getId() . '
                ';
        $stmt = $conn->query($sql)->fetchAllAssociative();


        $email = (new TemplatedEmail())
            ->from('support@asso-prefecture.com')
            ->to($user->getMail())
            ->subject('Suppression de votre réservation sur le site de l\'association de la préfecture de Haute Vienne')

            ->htmlTemplate('mailer/MailRemoveReservation.html.twig')
            ->context([
                'firstname' => $user->getFirstName(),
                'lastname' => $user->getLastName(),
                'articleName' => $reservationName,
                'articleQuantity' =>$reservationQuantity,
                'articlePrice' => $reservationPrice,
                'articles' =>$stmt,
            ]);
        $mailer->send($email);


        return $this->redirectToRoute('show_cart', ['id'=>$user->getId()], Response::HTTP_SEE_OTHER);
    }

}
