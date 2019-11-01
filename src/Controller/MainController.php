<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Model\Search;
use App\Provider\BeerProvider;
use App\Service\Search as SearchElastic;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

    /**
     * @var BeerProvider
     */
    private $beerProvider;

    /**
     * @var SearchElastic
     */
    private $search;

    /**
     * MainController constructor.
     * @param BeerProvider  $beerProvider
     * @param SearchElastic $search
     */
    public function __construct(BeerProvider $beerProvider, SearchElastic $search)
    {
        $this->beerProvider = $beerProvider;
        $this->search       = $search;
    }

    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $search = new Search();
        $form   = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);
//        $beers = $this->search->search('Irish');
        $beers = $this->beerProvider->getAll();
        if ($form->isSubmitted() && $form->isValid()) {
            dump($search);
        }
        dump($beers);

        return $this->render('main/index.html.twig', [
            'beers'  => $beers,
            'search' => $form->createView(),
        ]);
    }
}
