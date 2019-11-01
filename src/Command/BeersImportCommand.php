<?php
/**
 * todo refacto : remplace csv by json =>
 * https://data.opendatasoft.com/explore/dataset/open-beer-database%40public-us/export/?flg=fr
 */

namespace App\Command;

use App\Entity\Beer;
use App\Entity\Beer\Brewer;
use App\Entity\Beer\Category;
use App\Entity\Beer\Style;
use App\Entity\Coordinate;
use App\Kernel;
use App\Repository\Beer\BrewerRepository;
use App\Repository\Beer\CategoryRepository;
use App\Repository\Beer\StyleRepository;
use App\Repository\BeerRepository;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class BeersImportCommand extends Command
{
    const HEADER_NAME                           = 'name';
    const HEADER_ID                             = 'id';
    const HEADER_BREWERY_ID                     = 'brewery_id';
    const HEADER_CAT_ID                         = 'cat_id';
    const HEADER_STYLE_ID                       = 'style_id';
    const HEADER_ALCOHOL_BY_VOLUME              = 'abv';
    const HEADER_INTERNATIONAL_BITTERNESS_UNITS = 'ibu';
    const HEADER_STANDARD_REFERENCE_METHOD      = 'srm';
    const HEADER_UNIVERSAL_PRODUCT_CODE         = 'upc';
    const HEADER_FILEPATH                       = 'filepath';
    const HEADER_DESCRIPTION                    = 'descript';
    const HEADER_ADD_USER                       = 'add_user';
    const HEADER_LAST_MOD                       = 'last_mod';
    const HEADER_STYLE                          = 'style_name';
    const HEADER_CATEGORY                       = 'cat_name';
    const HEADER_BREWER                         = 'name_breweries';
    const HEADER_ADDRESS                        = 'address1';
    const HEADER_CITY                           = 'city';
    const HEADER_STATE                          = 'state';
    const HEADER_COUNTRY                        = 'country';
    const HEADER_COORDINATES                    = 'coordinates';
    const HEADER_WEBSITE                        = 'website';

    const FILE_DIR = '/var/data/beers/';
    const FILENAME = 'beers.csv';

    const DELIMITER = ';';

    protected static $defaultName = 'app:beers:import';

    /**
     * @var Kernel
     */
    private $kernel;

    private $project_dir;

    /**
     * @var Filesystem
     */
    private $filesystem;

    private $filepath;

    private $csvFile;

    private $jsonFile;

    private $header;

    private $body = [];

    /**
     * @var BrewerRepository
     */
    private $brewerRepository;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var StyleRepository
     */
    private $styleRepository;

    /**
     * @var BeerRepository
     */
    private $beerRepository;

    private $brewer           = [];

    private $style            = [];

    private $category         = [];

    private $beer             = [];

    private $created_brewer   = [];

    private $created_style    = [];

    private $created_category = [];

    private $created_beer     = [];

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct($name = null,
                                KernelInterface $kernel,
                                Filesystem $filesystem,
                                BrewerRepository $brewerRepository,
                                CategoryRepository $categoryRepository,
                                StyleRepository $styleRepository,
                                BeerRepository $beerRepository,
                                EntityManagerInterface $em
    )
    {
        $this->kernel      = $kernel;
        $this->project_dir = $kernel->getProjectDir();
        $this->filesystem  = $filesystem;
        $this->filepath    = $this->project_dir . self::FILE_DIR . self::FILENAME;

        $this->brewerRepository   = $brewerRepository;
        $this->categoryRepository = $categoryRepository;
        $this->styleRepository    = $styleRepository;
        $this->beerRepository     = $beerRepository;
        $this->em                 = $em;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Import les bieres depuis le fichier csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->getExistingData();

        $nb_created_beer = 0;
        $i               = 0;
        $progressBar     = new ProgressBar($output, count($this->body));
        $progressBar->start();

        foreach ($this->getData() as $row) {
            $progressBar->advance();

            if ($this->beerIsValid($row)) {
                continue;
            }

            $beer = new Beer();

            $beer->setName($row[self::HEADER_NAME])
                 ->setAlcohol((float)$row[self::HEADER_ALCOHOL_BY_VOLUME])
            ;

            $this->setBeerLastMod($beer, $row);
            $this->setBeerDescription($beer, $row);
            $this->setBeerStyle($beer, $row);
            $this->setBeerCategory($beer, $row);
            $this->setBeerBrewer($beer, $row);

            $beer->setBrewer($this->getBrewer($row));

            $this->em->persist($beer);

            $this->created_beer[] = md5($beer->getName() . $beer->getBrewer()->getName());

            $nb_created_beer++;
            $i++;

            if ($i % 100 == 0) {
                $this->em->flush();
            }
        }
        $progressBar->finish();
        $this->em->flush();
        $io->success('Bieres importé avec succés !');
    }

    private function getData()
    {
        $rawData = json_decode(
            file_get_contents('https://data.opendatasoft.com/explore/dataset/open-beer-database@public-us/download/?format=json&timezone=Europe/Paris'),
            true);
        return array_map(function ($row) {
            return $row['fields'];
        }, $rawData);
    }

    private function getExistingData()
    {
        foreach ($this->brewerRepository->findAll() as $brewer) {
            $this->brewer[$brewer->getName()] = $brewer;
        }

        foreach ($this->styleRepository->findAll() as $style) {
            $this->style[$style->getName()] = $style;
        }

        foreach ($this->categoryRepository->findAll() as $category) {
            $this->category[$category->getName()] = $category;
        }

        foreach ($this->beerRepository->findAll() as $beer) {
            $this->beer[] = md5($beer->getName() . $beer->getBrewer()->getName());
        }
    }

    /**
     * @param Beer $beer
     * @param      $row
     * @throws \Exception
     */
    private function setBeerLastMod(Beer &$beer, $row)
    {
        $dateTimeZone = new DateTimeZone('Europe/Paris');
        $lastMod      = array_key_exists(self::HEADER_LAST_MOD, $row) ?
            new DateTime($row[self::HEADER_LAST_MOD], $dateTimeZone) :
            new DateTime('NOW', $dateTimeZone);
        $beer->setLastMod($lastMod);
    }

    /**
     * @param Beer $beer
     * @param      $row
     */
    private function setBeerDescription(Beer &$beer, $row)
    {
        if (array_key_exists(self::HEADER_DESCRIPTION, $row)) {
            $beer->setDescription($row[self::HEADER_DESCRIPTION]);
        }
    }

    /**
     * @param Beer $beer
     * @param      $row
     */
    private function setBeerStyle(Beer &$beer, $row)
    {
        $beer->setStyle($this->getStyle(array_key_exists(self::HEADER_STYLE, $row) ?
            $row[self::HEADER_STYLE] :
            'NC'));
    }

    /**
     * @param Beer $beer
     * @param      $row
     */
    private function setBeerCategory(Beer &$beer, $row)
    {
        $beer->setCategory($this->getCategory(array_key_exists(self::HEADER_CATEGORY, $row) ?
            $row[self::HEADER_CATEGORY] :
            'NC'));
    }

    /**
     * @param $name
     * @return Style
     */
    private function getStyle($name)
    {
        if (array_key_exists($name, $this->style)) {
            return $this->style[$name];
        }
        if (array_key_exists($name, $this->created_style)) {
            return $this->created_style[$name];
        }

        $style                                  = new Style($name);
        $this->created_style[$style->getName()] = $style;
        return $style;
    }

    /**
     * @param $name
     * @return Category
     */
    private function getCategory($name)
    {
        if (array_key_exists($name, $this->category)) {
            return $this->category[$name];
        }

        if (array_key_exists($name, $this->created_category)) {
            return $this->created_category[$name];
        }

        $category                                     = new Category($name);
        $this->created_category[$category->getName()] = $category;
        return $category;
    }

    /**
     * @param $row
     * @return Brewer
     */
    private function getBrewer($row)
    {
        if (array_key_exists($row[self::HEADER_BREWER], $this->brewer)) {
            return $this->brewer[$row[self::HEADER_BREWER]];
        }

        if (array_key_exists($row[self::HEADER_BREWER], $this->created_brewer)) {
            return $this->created_brewer[$row[self::HEADER_BREWER]];
        }
        $brewer = new Brewer($row[self::HEADER_BREWER]);
        if (array_key_exists(self::HEADER_COUNTRY, $row)) {
            $brewer->setCountry($row[self::HEADER_COUNTRY]);
        }
        if (array_key_exists(self::HEADER_CITY, $row)) {
            $brewer->setCity($row[self::HEADER_CITY]);
        }
        if (array_key_exists(self::HEADER_ADDRESS, $row)) {
            $brewer->setAddress($row[self::HEADER_ADDRESS]);
        }
        if (array_key_exists(self::HEADER_WEBSITE, $row)) {
            $brewer->setWebsite($row[self::HEADER_WEBSITE]);
        }
        if (array_key_exists(self::HEADER_STATE, $row)) {
            $brewer->setState($row[self::HEADER_STATE]);
        }

        if (array_key_exists(self::HEADER_COORDINATES, $row)) {
            $brewer->setCoordinate($this->getCoordinate($row[self::HEADER_COORDINATES]));
        }

        $this->created_brewer[$brewer->getName()] = $brewer;
        return $brewer;
    }

    /**
     * @param $coordinate
     * @return Coordinate|null
     */
    private function getCoordinate($coordinate)
    {

        return new Coordinate(
            (float)$coordinate[0],
            (float)$coordinate[1]
        );
    }

    /**
     * @param $row
     * @return bool
     */
    private function beerIsValid($row)
    {
        if ((int)$row[self::HEADER_ID] === 0) {
            return true;
        }
        if (in_array(md5($row[self::HEADER_NAME] . $row[self::HEADER_BREWER]), $this->beer)
            || in_array(md5($row[self::HEADER_NAME] . $row[self::HEADER_BREWER]), $this->created_beer)) {
            return true;
        }
        return false;
    }
}

