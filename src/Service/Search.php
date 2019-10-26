<?php

namespace App\Service;

use App\Entity\Beer;
use Elastica\Query\BoolQuery;
use Elastica\Query\Match;
use Elastica\Type;

class Search
{

    private $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    public function search($word)
    {
        $boolQuery  = new BoolQuery();
        $fieldQuery = (new Match())
            ->setFieldQuery('name', $word)
            ->setFieldFuzziness('name', 2)
            ->setFieldParam('name', 'analyzer', 'my_analyzer')
        ;
        $boolQuery->addShould($fieldQuery);

        $elastica_search = $this->type->search($boolQuery);

        $queryToSize = $elastica_search
            ->getQuery()
            ->setSize(40)->setFrom(0)
            ->setMinScore(3)
        ;

        $elastica_search = $this->type->search($queryToSize);
        $results         = $elastica_search->getResults();

        $count     = $elastica_search->count();
        $totalHits = $elastica_search->getTotalHits();
        dump($elastica_search);

        if (!$count) {
            return [];
        }
        return $this->getHydratedObjectsFromRawResults($results);

    }


    /**
     * Transforme le tableau de result RAW en tableau d'objets PageContent
     *
     * @param array $results
     * @return PageContent[]
     */
    private function getHydratedObjectsFromRawResults(array $results): array
    {
        return array_map(function ($result) {
            $beer = new Beer();
            $b
        }, $results);
        $objects = [];
        foreach ($results as $result) {
            /** @var Result $result */
            $source    = $result->getSource();
            $objects[] = (new PageContent($source['url']))
                ->setMetaTitle($source['metaTitle'])
                ->setMetaDescription($source['metaDescription'])
            ;
        }
        return $objects;
    }

}