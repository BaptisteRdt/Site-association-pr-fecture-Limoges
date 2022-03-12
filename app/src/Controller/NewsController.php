<?php

namespace App\Controller;

use App\Entity\ViewLog;
use App\Entity\News;
use App\Form\NewsType;
use App\Repository\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsController extends AbstractController
{
    private function registerVisit(EntityManagerInterface $entityManager)
    {
        $viewLog = new ViewLog();
        $viewLog->setDate(new \DateTime("now", new \DateTimeZone("Europe/Paris")));
        $entityManager->persist($viewLog);
        $entityManager->flush();
    }

    #[Route('/admin/news/', name: 'news_index', methods: ['GET'])]
    public function index(NewsRepository $newsRepository): Response
    {
        return $this->render('news/index.html.twig', [
            'news' => $newsRepository->findAll(),
        ]);
    }

    #[Route('/admin/news/new', name: 'news_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $news = new News();
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get("image")->getData();
            if ($image != null){
                $name = md5(uniqid()).'.'.$image->guessExtension();
                $image->move('ImageNews', $name);
                $news->setImageName($name);
            }

            $entityManager->persist($news);
            $entityManager->flush();

            return $this->redirectToRoute('news_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('news/new.html.twig', [
            'news' => $news,
            'form' => $form,
        ]);
    }

    #[Route('/news/{id}', name: 'news_show', methods: ['GET'])]
    public function show(News $news, EntityManagerInterface $entityManager): Response
    {
        $this->registerVisit($entityManager);
        $entity = $entityManager->getRepository(News::class)->findBy(array(), array('id' => 'DESC'),5 ,0);

        return $this->render('news/show.html.twig', [
            'news' => $news,
        ]);
    }

    #[Route('/admin/news/{id}/isPinned', name: 'news_pin', methods: ['GET', 'POST'])]
    public function pinned(Request $request, News $news, EntityManagerInterface $entityManager)
    {
       if ($news->getIsPinned()){
           $news->setIsPinned(false);
       }else{
           $news->setIsPinned(true);
       }
        $entityManager->flush();
        return $this->redirectToRoute('news_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/admin/news/{id}/edit', name: 'news_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, News $news, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($news->getImageName() != null){
                $filesystem = new Filesystem();
                $filesystem->remove("ImageNews/" .$news->getImageName());
            }

            $image = $form->get("image")->getData();
            if ($image != null){
                $name = md5(uniqid()).'.'.$image->guessExtension();
                $image->move('ImageNews', $name);

                $news->setImageName($name);
            }

            $entityManager->flush();

            return $this->redirectToRoute('news_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('news/edit.html.twig', [
            'news' => $news,
            'form' => $form,
        ]);
    }

    #[Route('/admin/news/{id}', name: 'news_delete', methods: ['POST'])]
    public function delete(Request $request, News $news, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$news->getId(), $request->request->get('_token'))) {
            if($news->getImageName() != null){
                $filesystem = new Filesystem();
                $filesystem->remove("ImageNews/" .$news->getImageName());
            }
            $entityManager->remove($news);
            $entityManager->flush();
        }

        return $this->redirectToRoute('news_index', [], Response::HTTP_SEE_OTHER);
    }
}
