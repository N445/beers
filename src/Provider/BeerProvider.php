<?php

namespace App\Provider;

use App\Repository\BeerRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 18/10/2019
 * Time: 21:59
 */
class BeerProvider
{

    private $cache;

    /**
     * @var BeerRepository
     */
    private $beerRepository;

    /**
     * BeerProvider constructor.
     * @param BeerRepository $beerRepository
     */
    public function __construct(BeerRepository $beerRepository)
    {
        $this->cache          = new FilesystemAdapter();
        $this->beerRepository = $beerRepository;
    }

    /**
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getAll()
    {
        return $this->cache->get('beer_all', function (ItemInterface $item) {
            $item->expiresAfter(3600);
            return $this->beerRepository->getBeers();
        });
    }
}