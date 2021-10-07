<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="accueil")
     */
    public function afficheHome(){
        return $this->render('accueil.html.twig',[]);
    }

        /**
     * @Route("/cgu", name="cgu")
     */
    public function afficheRegle(){
        return $this->render('cgu.html.twig',[]);
    }
}
