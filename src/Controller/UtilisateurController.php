<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurMdpType;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/utilisateur')]
class UtilisateurController extends AbstractController
{
    /**
     * @Route("/", name="liste_utilisateurs")
     */
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        return $this->render('utilisateur/liste-utilisateurs.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll(),
        ]);
    }
    
    /**
     * @Route("/ajout", name="ajout_utilisateur")
    */
    public function ajout(Request $request, UserPasswordHasherInterface $utilisateurPasswordHasherInterface): Response
    {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurMdpType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur->setPassword(
                $utilisateurPasswordHasherInterface->hashPassword(
                        $utilisateur,
                        $form->get('password')->getData()
                    )
                );
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($utilisateur);
            $entityManager->flush();

            return $this->redirectToRoute('liste_utilisateurs', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('utilisateur/ajout.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="details_utilisateur")
    */
    public function detail(Utilisateur $utilisateur): Response
    {
        return $this->render('utilisateur/details.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    /**
     * @Route("/{id}/modif", name="modif_utilisateur")
    */
    public function modif(Request $request, Utilisateur $utilisateur): Response
    {
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('liste_utilisateurs', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('utilisateur/modif.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }
    
    /**
     * @Route("/{id}/sup", name="sup_utilisateur")
    */
    public function sup(Request $request, Utilisateur $utilisateur): Response
    {

        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->query->get('csrf'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($utilisateur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('liste_utilisateurs', [], Response::HTTP_SEE_OTHER);
    }
}
