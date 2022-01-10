<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Reservation;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;

class ShopController extends AbstractController
{
    #[Route('/boutique', name: 'shop')]
    public function index(ArticleRepository $articleRepository): Response
    {

        return $this->render('shop/index.html.twig', [
            'controller_name' => 'ShopController',
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/boutique/{id}', name: 'boutique_detail', methods: ['GET', 'POST'])]
    public function show(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation)
            ->remove('user')
            ->remove('Article')
            ->remove('price');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $quantity = $form->get('quantity')->getData();
            $totalQuantity = $article->getQuantite();
            $reservedQuantity = 0;
            $reservationArticle = $entityManager->getRepository(Reservation::class)->findBy(['Article' => $article]);
            
            for ($i=0; $i < count($reservationArticle); $i++) { 
               $reservedQuantity += $reservationArticle[$i]->getQuantity();
            }

            if ($quantity > 0 && $quantity <= $totalQuantity - $reservedQuantity) {
                $reservation->setUser($this->getUser());
                $reservation->setArticle($article);
                $reservation->setPrice($article->getPrix() * $form->get('quantity')->getData());
    
                $entityManager->persist($reservation);
                $entityManager->flush();
    
                $this->addFlash('success', 'Votre réservation a bien été prise en compte');
               
            }  
            
            


            return $this->redirectToRoute('shop', [], Response::HTTP_SEE_OTHER);
        }   

        return $this->renderForm('shop/detail.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }
}
