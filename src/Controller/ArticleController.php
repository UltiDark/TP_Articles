<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/article')]
class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="liste_articles")
     */
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/liste-articles.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    /**
     * @Route("/ajout", name="ajout_article")
    */
    public function ajout(Request $request, SluggerInterface $slugger, UserInterface $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ECRIVAIN');

        $article = new Article();
        $article->setDatePublication(new \DateTime('now'));

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setIdAuteur($user);
            $lienFile = $form->get('image')->getData();
            if ($lienFile) {
                $originalFilename = pathinfo($lienFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$lienFile->guessExtension();
                try {
                    $lienFile->move(
                        $this->getParameter('img_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $article->setImage($newFilename);
            }
            else{
                $article->setImage('default.jpg');
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('ajout_article', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('article/ajout.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="details_article")
    */
    public function show( Request $request, Article $article, UserInterface $user): Response
    {   
        $commentaire = new Commentaire();
        $commentaire->setDatePublication(new \DateTime('now'));

        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire->setIdArticle($article);
            $commentaire->setIdAuteur($user);
            $commentaire->setNome(0);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($commentaire);
            $entityManager->flush();

            $this->addFlash('success', 'Un nouveau article a été ajouté !');

            return $this->redirectToRoute('details_article', ['id' => $article->getId()]);
        }

        return $this->render('article/details.html.twig', [
            'article' => $article,
            'commentaire' => $commentaire,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/modif", name="modif_article")
    */
    public function modif(Request $request, Article $article, SluggerInterface $slugger, UserInterface $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ECRIVAIN');
        if($user->getId() != $article->getIdAuteur()){
            return $this->redirectToRoute('liste_articles', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $lienFile = $form->get('image')->getData();
            if ($lienFile) {
                $originalFilename = pathinfo($lienFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$lienFile->guessExtension();
                try {
                    $lienFile->move(
                        $this->getParameter('img_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $article->setImage($newFilename);
            }
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Une modification a été opéré !');

            return $this->redirectToRoute('liste_articles');
        }

        return $this->renderForm('article/modif.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/sup", name="sup_article")
    */
    public function sup(Request $request, Article $article): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->query->get('csrf'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();

            $this->addFlash('success', 'Un article a été supprimé !');
        }
        else{
            $this->addFlash('error', 'La suppression a échoué !');
        }

        return $this->redirectToRoute('liste_articles');
    }
}
