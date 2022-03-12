<?php

namespace App\Controller;

use App\Entity\ViewLog;
use App\Repository\DocumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class DocumentPageController extends AbstractController
{

    private function registerVisit(EntityManagerInterface $entityManager)
    {
        $viewLog = new ViewLog();
        $viewLog->setDate(new \DateTime("now", new \DateTimeZone("Europe/Paris")));
        $entityManager->persist($viewLog);
        $entityManager->flush();
    }

    #[Route('/documentPage', name: 'document_page')]
    public function index(EntityManagerInterface $entityManager, DocumentRepository $documentRepository): Response
    {
        $this->registerVisit($entityManager);

        $documentPinned = $documentRepository->allDocumentPinned();
        $documentPinnedAndAdherent = $documentRepository->allDocumentPinnedAndAdherent();


        return $this->render('document_page/index.html.twig', [
            'controller_name' => 'DocumentPageController',
            'documentPinneds' => $documentPinned,
            'documentAdherents' => $documentPinnedAndAdherent,
        ]);
    }
}
