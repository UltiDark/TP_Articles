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

            $this->addFlash('success', 'Un nouvel utilisateur a été créé !');

            return $this->redirectToRoute('liste_utilisateurs', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('commun/formulaire.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
            'titre' => 'Ajouter un utilisateur'
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
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Une modification a été opéré !');

            return $this->redirectToRoute('liste_utilisateurs', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('commun/formulaire.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
            'titre' => 'Modifier un utilisateur'
        ]);
    }
    
    /**
     * @Route("/{id}/sup", name="sup_utilisateur")
    */
    public function sup(Request $request, Utilisateur $utilisateur): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->query->get('csrf'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', 'Un nouvel utilisateur a été supprimé !');

        }
        else{
            $this->addFlash('error', 'La suppression avait échoué !');
        }

        return $this->redirectToRoute('liste_utilisateurs', [], Response::HTTP_SEE_OTHER);
    }
}
