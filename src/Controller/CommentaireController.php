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

            $this->addFlash('success', 'Un nouveau commentaire a été ajouté !');

            return $this->redirectToRoute('liste_commentaires');
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
        if (!$this->isGranted('ROLE_MODO')) {
            $this->addFlash('error', 'accès interdit');
            return $this->redirectToRoute('app_login');
        }
        if ($this->isCsrfTokenValid('delete'.$commentaire->getId(), $request->query->get('csrf'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($commentaire);
            $entityManager->flush();

            $this->addFlash('success', 'Un commentaire a été supprimé !');

        }
        else{
            $this->addFlash('error', 'La suppression a échoué !');
        }

        return $this->redirectToRoute('liste_commentaires', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/nome/{id}/plus", name="plus_note")
     */
    public function notePlus(Commentaire $commentaire)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $commentaire->setNome($commentaire->getNome()+1);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('details_article', ['id' => strval($commentaire->getIdArticle()->getId())]);
    }

    /**
     * @Route("/nome/{id}/moins", name="moins_note")
     */
    public function noteMoins(Commentaire $commentaire)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $commentaire->setNome($commentaire->getNome()-1);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('details_article', ['id' => strval($commentaire->getIdArticle()->getId())]);
    }
}
