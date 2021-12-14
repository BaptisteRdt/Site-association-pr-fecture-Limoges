<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
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

        return $this->render('admin/show_cart.html.twig', [
            'controller_name' => 'PanierController',
            'articles'=>$articles,
            'user'=>$user
        ]);
    }

    #[Route('/admin/panier/confirm/{user}/{id}', name: 'confirm_purchase')]
    public function confirm_purchase($id, User $user, EntityManagerInterface $em){

        $cart= $user->getCart();

        $article = $em->getRepository(Article::class)
            ->findOneBy(['id' => +$id]);

        if ($article){
            if(array_key_exists(+$id, $cart)&& $cart[+$id]>0){
                $cart[+$id] = $cart[+$id] -1;
            }
        }
        $user->setCart($cart);
        $em->flush();

        return $this->redirectToRoute('show_cart', array('id'=>$user->getId()), Response::HTTP_SEE_OTHER);
    }


    #[Route('/admin/panier/remove/{user}/{id}', name: 'remove_reservation')]
    public function remove_reservation($id, User $user, EntityManagerInterface $em){

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

        return $this->redirectToRoute('show_cart', array('id'=>$user->getId()), Response::HTTP_SEE_OTHER);
    }

}
