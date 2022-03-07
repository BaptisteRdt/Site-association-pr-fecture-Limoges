<?php

namespace App\Controller;

use App\Entity\Office;
use App\Form\OfficeType;
use App\Repository\OfficeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/bureau')]
class OfficeController extends AbstractController
{
    private function registerVisit(EntityManagerInterface $entityManager)
    {
        $viewLog = new ViewLog();
        $viewLog->setDate(new \DateTime("now", new \DateTimeZone("Europe/Paris")));
        $entityManager->persist($viewLog);
        $entityManager->flush();
    }

    #[Route('/', name: 'office_index', methods: ['GET'])]
    public function index(OfficeRepository $officeRepository, EntityManagerInterface $entityManager): Response
    {
        $this->registerVisit($entityManager);
        $entity = $entityManager->getRepository(News::class)->findBy(array(), array('id' => 'DESC'),5 ,0);

        return $this->render('office/index.html.twig', [
            'offices' => $officeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'office_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $office = new Office();
        $form = $this->createForm(OfficeType::class, $office);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get("image")->getData();

            if ($image != null){
                $name = md5(uniqid()).'.'.$image->guessExtension();
                $image->move('ImageOffice', $name);

                $office->setImageName($name);
            }

            $entityManager->persist($office);
            $entityManager->flush();

            return $this->redirectToRoute('office_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('office/new.html.twig', [
            'office' => $office,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'office_show', methods: ['GET'])]
    public function show(Office $office, EntityManagerInterface $entityManager): Response
    {
        $this->registerVisit($entityManager);
        $entity = $entityManager->getRepository(News::class)->findBy(array(), array('id' => 'DESC'),5 ,0);

        return $this->render('office/show.html.twig', [
            'office' => $office,
        ]);
    }

    #[Route('/{id}/edit', name: 'office_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Office $office, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OfficeType::class, $office);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($office->getImageName() != null){
                $filesystem = new Filesystem();
                $filesystem->remove("ImageOffice/" .$office->getImageName());
            }

            $image = $form->get("image")->getData();
            if ($image != null){
                $name = md5(uniqid()).'.'.$image->guessExtension();
                $image->move('ImageOffice', $name);

                $office->setImageName($name);
            }

            $entityManager->flush();

            return $this->redirectToRoute('office_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('office/edit.html.twig', [
            'office' => $office,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'office_delete', methods: ['POST'])]
    public function delete(Request $request, Office $office, EntityManagerInterface $entityManager): Response
    {


        if ($this->isCsrfTokenValid('delete'.$office->getId(), $request->request->get('_token'))) {
            if($office->getImageName() != null){
                $filesystem = new Filesystem();
                $filesystem->remove("ImageOffice/" .$office->getImageName());
            }
            $entityManager->remove($office);
            $entityManager->flush();
        }

        return $this->redirectToRoute('office_index', [], Response::HTTP_SEE_OTHER);
    }
}
