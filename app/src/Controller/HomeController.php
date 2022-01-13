<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\News;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $em): Response
    {

        $entity = $em->getRepository(News::class)->findBy(array(), array('id' => 'DESC'),5 ,0);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $images =[];
        foreach($entity as $article){
            $imageName = $article->getImageName();
           $images[] = $imageName;
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'articles'=>$entity,
            'images'=>$images
        ]);
    }
}
