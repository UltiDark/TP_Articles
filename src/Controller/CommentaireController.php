<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\CommentaireRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/commentaire")
 */
class CommentaireController extends AbstractController
{
    /**
     * @Route("/", name="liste_commentaires")
     */
    public function index(CommentaireRepository $commentaireRepository): Response
    {
        return $this->render('commentaire/liste-commentaires.html.twig', [
            'commentaires' => $commentaireRepository->findAll(),
        ]);
    }

    /**
     * @Route("/ajout", name="ajout_commentaire")
    */
    public function ajout(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $commentaire = new Commentaire();
        $commentaire->setDatePublication(new \DateTime('now'));

        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($commentaire);
            $entityManager->flush();

            return $this->redirectToRoute('liste_commentaires', [], Response::HTTP_SEE_OTHER);
        }

        return [
            'commentaire' => $commentaire,
            'form' => $form,
        ];
    }

    /**
     * @Route("/{id}/sup", name="sup_commentaire")
    */
    public function sup(Request $request, Commentaire $commentaire): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$commentaire->getId(), $request->query->get('csrf'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($commentaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('liste_commentaires', [], Response::HTTP_SEE_OTHER);
    }
}
