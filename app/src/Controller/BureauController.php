<?php

namespace App\Controller;

use App\Entity\ViewLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OfficeRepository;

class BureauController extends AbstractController
{
    private function registerVisit(EntityManagerInterface $entityManager)
    {
        $viewLog = new ViewLog();
        $viewLog->setDate(new \DateTime("now", new \DateTimeZone("Europe/Paris")));
        $entityManager->persist($viewLog);
        $entityManager->flush();
    }

    #[Route('/bureau', name: 'bureau')]
    public function index(OfficeRepository $officeRepository, EntityManagerInterface $em): Response
    {
        $this->registerVisit($em);

        return $this->render('bureau/index.html.twig', [
            'controller_name' => 'BureauController',
            'offices' => $officeRepository->findAll(),
        ]);
    }
}
