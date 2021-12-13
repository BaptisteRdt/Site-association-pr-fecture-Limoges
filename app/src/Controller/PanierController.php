<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;


class PanierController extends AbstractController
{
    #[Route('/panier', name: 'panier')]
    public function index(EntityManagerInterface $em): Response
    {
        $cart= $this->getUser()->getCart();
        $articles= $em->getRepository(Article::class)->findBy(array('id' => array_keys($cart)));

        foreach ($articles as $article){
            $article->setQuantite($cart[$article->getId()]);
        }
        return $this->render('panier/index.html.twig', [
            'controller_name' => 'PanierController',
            'articles'=>$articles,
        ]);
    }
    #[Route('/panier/add/{id}', name: 'panier_add')]
    public function add($id, EntityManagerInterface $em){
        $user = $this->getUser();
        $cart = $user->getCart();

        $article = $em->getRepository(Article::class)
            ->findOneBy(['id' => +$id]);

        if ($article && $article->getQuantite()>0){
            if(!array_key_exists(+$id, $cart)){
                $cart[+$id] = 1;
            }else{
                $cart[+$id] = $cart[+$id] +1;
            }
            $article->setQuantite($article->getQuantite()-1);
        }


        $user->setCart($cart);
        $em->flush();

        return $this->redirectToRoute('shop', [], Response::HTTP_SEE_OTHER);

    }

    #[Route('/panier/remove/{id}', name: 'panier_remove')]
    public function remove($id, EntityManagerInterface $em){
        $user = $this->getUser();
        $cart = $user->getCart();

        $article = $em->getRepository(Article::class)
            ->findOneBy(['id' => +$id]);

        if ($article){
            if(array_key_exists(+$id, $cart)&& $cart[+$id]>0){
                $cart[+$id] = $cart[+$id] -1;
                $article->setQuantite($article->getQuantite()+1);
            }
        }

        $user->setCart($cart);
        $em->flush();

        return $this->redirectToRoute('shop', [], Response::HTTP_SEE_OTHER);

    }
}
