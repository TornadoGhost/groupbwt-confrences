<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TempController extends AbstractController
{
    /**
     * @Route("/temp", name="temp_page")
     */
    public function index(): Response
    {
        return $this->render('temp.html.twig', [
            'controller_name' => 'TempController',
        ]);
    }
}
