<?php

namespace App\Model;

class Search
{
    /**
     * @var string
     */
    private $word;

    /**
     * @return string
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * @param string $word
     * @return Search
     */
    public function setWord(string $word): Search
    {
        $this->word = $word;
        return $this;
    }
}