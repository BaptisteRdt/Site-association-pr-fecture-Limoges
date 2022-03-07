<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\News;
use App\Entity\ViewLog;

class HomeController extends AbstractController
{
    private function registerVisit(EntityManagerInterface $entityManager)
    {
        $viewLog = new ViewLog();
        $viewLog->setDate(new \DateTime("now", new \DateTimeZone("Europe/Paris")));
        $entityManager->persist($viewLog);
        $entityManager->flush();
    }

    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $em): Response
    {
        $this->registerVisit($em);
        
        $entity = $em->getRepository(News::class)->findBy(array(), array('id' => 'DESC'),5 ,0);

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
