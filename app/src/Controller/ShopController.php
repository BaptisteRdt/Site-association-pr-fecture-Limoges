<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Reservation;
use App\Entity\ViewLog;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;

class ShopController extends AbstractController
{

    private function registerVisit(EntityManagerInterface $entityManager)
    {
        $viewLog = new ViewLog();
        $viewLog->setDate(new \DateTime("now", new \DateTimeZone("Europe/Paris")));
        $entityManager->persist($viewLog);
        $entityManager->flush();
    }

    #[Route('/boutique', name: 'shop')]
    public function index(ArticleRepository $articleRepository, EntityManagerInterface $entityManager): Response
    {
        $this->registerVisit($entityManager);

        return $this->render('shop/index.html.twig', [
            'controller_name' => 'ShopController',
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/boutique/{id}', name: 'boutique_detail', methods: ['GET', 'POST'])]
    public function show(Article $article, Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {

        $this->registerVisit($entityManager);

        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation)
            ->remove('user')
            ->remove('Article')
            ->remove('price');
        $form->handleRequest($request);

        $totalQuantity = $article->getQuantite();
        $reservedQuantity = 0;
        $reservationArticle = $entityManager->getRepository(Reservation::class)->findBy(['Article' =>$article]);
            
        for ($i=0; $i < count($reservationArticle); $i++) { 
           $reservedQuantity += $reservationArticle[$i]->getQuantity();
        }


        if ($form->isSubmitted() && $form->isValid()) {

            $quantity = $form->get('quantity')->getData();

            if ($quantity > 0 && $quantity <= $totalQuantity - $reservedQuantity) {
                $reservation->setUser($this->getUser());
                $reservation->setArticle($article);
                $reservation->setPrice($article->getPrix() * $form->get('quantity')->getData());

                $entityManager->persist($reservation);
                $entityManager->flush();


                $cart= $reservation->getUser()->getCart();
                $articles= $entityManager->getRepository(Article::class)->findBy(array('id' => array_keys($cart)));
                foreach ($articles as $article){
                    $article->setQuantite($cart[$article->getId()]);
                }

                $conn = $entityManager->getConnection();

                $sql = '
                SELECT a.name, r.quantity, r.price, r.id FROM reservation r, article a
                WHERE r.article_id = a.id and r.user_id = ' . $reservation->getUser()->getId() . '
                ';
                $stmt = $conn->query($sql)->fetchAllAssociative();

                $email = (new TemplatedEmail())
                    ->from('support@asso-prefecture.com')
                    ->to($reservation->getUser()->getMail())
                    ->subject('Commande sur le site de l\'association de la préfecture de Haute Vienne')

                    ->htmlTemplate('mailer/MailReservation.html.twig')
                    ->context([
                        'firstname' => $reservation->getUser()->getFirstName(),
                        'lastname' => $reservation->getUser()->getLastName(),
                        'articleName' => $reservation->getArticle()->getName(),
                        'articleQuantity' =>$reservation->getQuantity(),
                        'articlePrice' => $reservation->getPrice(),
                        'articles' =>$stmt,
                    ]);
                $mailer->send($email);

            }  

            return $this->redirectToRoute('shop', [], Response::HTTP_SEE_OTHER);
        }   

        return $this->renderForm('shop/detail.html.twig', [
            'article' => $article,
            'form' => $form,
            'remainingQuantity' => $totalQuantity - $reservedQuantity,
        ]);
    }
}
