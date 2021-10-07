<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ContactType;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MailController extends AbstractController {
    /**
     * @Route("/mail", name="envoie_mail")
     */
    public function index(Request $request, MailerInterface $mi): Response {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {        

        $email = new Email;
        $email
            ->from($form->getData()['mail'])
            ->to('alt.d4-2o62goq4@yopmail.com')
            ->subject($form->getData()['objet'])
            ->text($form->getData()['contenu']);

        $mi->send($email);

        $this->addFlash('success', 'Votre mail a été envoyé !');

        return $this->redirectToRoute('envoie_mail');
        }
        
        return $this->render('commun/formulaire.html.twig', [
            'titre' => 'Envoyer un mail',
            'form' => $form->createView()
        ]);
    }
}