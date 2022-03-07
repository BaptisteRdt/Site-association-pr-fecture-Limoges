<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Form\ContactReplyType;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    private function registerVisit(EntityManagerInterface $entityManager)
    {
        $viewLog = new ViewLog();
        $viewLog->setDate(new \DateTime("now", new \DateTimeZone("Europe/Paris")));
        $entityManager->persist($viewLog);
        $entityManager->flush();
    }

    #[Route('/admin/contact/', name: 'contact_index', methods: ['GET'])]
    public function index(ContactRepository $contactRepository): Response
    {
        return $this->render('contact/index.html.twig', [
            'contacts' => $contactRepository->findAll(),
        ]);
    }

    #[Route('/contact/', name: 'contact_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {

        $this->registerVisit($entityManager);
        $entity = $entityManager->getRepository(News::class)->findBy(array(), array('id' => 'DESC'),5 ,0);

        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contact->setDate(new \DateTime('now'));

            $email = (new TemplatedEmail())
                ->from('support@asso-prefecture.com')
                ->to($form->getData()->getMailaddress())
                ->subject('Demande de contact sur le site de l\'association')

                ->htmlTemplate('mailer/MailSendingContact.html.twig')
                ->context([
                    'firstname' => $form->getData()->getFirstname(),
                    'lastname' => $form->getData()->getName(),
                    'content' => $form->getData()->getMessage(),
                    'subject' =>$form->getData()->getSubject(),
                ]);

            $mailer->send($email);

            $entityManager->persist($contact);
            $entityManager->flush();

            return $this->redirectToRoute('contact_new', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contact/new.html.twig', [
            'contact' => $contact,
            'form' => $form,
        ]);
    }

    #[Route('/admin/contact/{id}', name: 'contact_show', methods: ['GET'])]
    public function show(Contact $contact): Response
    {
        return $this->render('contact/show.html.twig', [
            'contact' => $contact,
        ]);
    }

    #[Route('/admin/contact/{id}/reply', name: 'contact_reply', methods: ['POST', 'GET'])]
    public function reply(Request $request, EntityManagerInterface $em, Contact $contact, MailerInterface $mailer) : Response
    {
        $form = $this->createForm(ContactReplyType::class, $contact);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = (new TemplatedEmail())
                ->from('support@asso-prefecture.com')
                ->to($form->getData()->getMailaddress())
                ->subject('Réponse du message du site de l\'association de la préfecture de Haute Vienne')

                ->htmlTemplate('mailer/MailAnswerAdminContact.html.twig')
                ->context([
                    'firstname' => $contact->getFirstname(),
                    'lastname' => $contact->getName(),
                    'content' => $contact->getMessage(),
                    'subject' =>$contact->getSubject(),
                    'contentAdmin' => $contact->getReply(),
                ]);
            $mailer->send($email);
            $em->remove($contact);
            $em->flush();
            return $this->redirectToRoute('contact_index',[], Response::HTTP_SEE_OTHER);
        }
        return $this->render('contact/reply.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    #[Route('/admin/contact/delete/{id}', name: 'contact_delete')]
    public function delete(Request $request, Contact $contact, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contact->getId(), $request->request->get('_token'))) {
            $entityManager->remove($contact);
            $entityManager->flush();
        }
        return $this->redirectToRoute('contact_index', [], Response::HTTP_SEE_OTHER);
    }
}
