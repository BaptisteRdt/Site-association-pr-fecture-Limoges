<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\OfficeRepository;

class BureauController extends AbstractController
{

    #[Route('/bureau', name: 'bureau')]
    public function index(OfficeRepository $officeRepository): Response
    {
        return $this->render('bureau/index.html.twig', [
            'controller_name' => 'BureauController',
            'offices' => $officeRepository->findAll(),
        ]);
    }
}
