<?php

namespace App\Controller;

use App\Repository\BeerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     * @param BeerRepository $beerRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BeerRepository $beerRepository)
    {
        return $this->render('main/index.html.twig', [
            'beers' => $beerRepository->findAll(),
        ]);
    }
}
