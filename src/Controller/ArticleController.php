<?php

namespace App\Controller;

use DateTime;
use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
    public function ajout(Request $request, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ECRIVAIN');

        $article = new Article();
        $article->setDatePublication(new \DateTime('now'));

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
            else{
                $article->setImage('default.jpg');
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('liste_articles', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('article/ajout.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="details_article")
    */
    public function show(Article $article): Response
    {

        return $this->render('article/details.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/{id}/modif", name="modif_article")
    */
    public function modif(Request $request, Article $article, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ECRIVAIN');

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

            return $this->redirectToRoute('liste_articles', [], Response::HTTP_SEE_OTHER);
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
        }

        return $this->redirectToRoute('liste_articles', [], Response::HTTP_SEE_OTHER);
    }
}
