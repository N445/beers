<?php

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
    const HEADER_NAME                           = 'Name';
    const HEADER_ID                             = 'id';
    const HEADER_BREWERY_ID                     = 'brewery_id';
    const HEADER_CAT_ID                         = 'cat_id';
    const HEADER_STYLE_ID                       = 'style_id';
    const HEADER_ALCOHOL_BY_VOLUME              = 'Alcohol By Volume';
    const HEADER_INTERNATIONAL_BITTERNESS_UNITS = 'International Bitterness Units';
    const HEADER_STANDARD_REFERENCE_METHOD      = 'Standard Reference Method';
    const HEADER_UNIVERSAL_PRODUCT_CODE         = 'Universal Product Code';
    const HEADER_FILEPATH                       = 'filepath';
    const HEADER_DESCRIPTION                    = 'Description';
    const HEADER_ADD_USER                       = 'add_user';
    const HEADER_LAST_MOD                       = 'last_mod';
    const HEADER_STYLE                          = 'Style';
    const HEADER_CATEGORY                       = 'Category';
    const HEADER_BREWER                         = 'Brewer';
    const HEADER_ADDRESS                        = 'Address';
    const HEADER_CITY                           = 'City';
    const HEADER_STATE                          = 'State';
    const HEADER_COUNTRY                        = 'Country';
    const HEADER_COORDINATES                    = 'Coordinates';
    const HEADER_WEBSITE                        = 'Website';

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
        $this->csvFile = file($this->filepath);
        $this->setHeader();
        $this->setBody();
        $nb_created_beer = 0;
        $i               = 0;
        $progressBar     = new ProgressBar($output, count($this->body));
        $progressBar->start();

        foreach ($this->body as $row) {
            $progressBar->advance();
            if (in_array($row[self::HEADER_NAME], $this->beer) || in_array($row[self::HEADER_NAME], $this->created_beer)) {
                continue;
            }
            $beer = new Beer();
            $beer->setName($row[self::HEADER_NAME])
                 ->setDescription($row[self::HEADER_DESCRIPTION])
                 ->setAlcohol((float)$row[self::HEADER_ALCOHOL_BY_VOLUME])
                 ->setLastMod(new DateTime($row[self::HEADER_LAST_MOD], new DateTimeZone('Europe/Paris')))
            ;
            $beer->setStyle($this->getStyle($row[self::HEADER_STYLE]));
            $beer->setCategory($this->getCategory($row[self::HEADER_CATEGORY]));
            $beer->setBrewer($this->getBrewer($row));
            $this->em->persist($beer);
            $this->created_beer[] = $beer->getName();
            $nb_created_beer++;
            $i++;
//            $beer->getName();
            if ($i % 500) {
                $this->em->flush();
            }
        }
        $progressBar->finish();
        $this->em->flush();
        $io->success('Bieres importé avec succés !');
    }


    private function setHeader()
    {
        $this->header = explode(self::DELIMITER, trim(preg_replace('/\s\s+/', ' ', $this->csvFile[0])));
        array_shift($this->csvFile);
    }

    private function setBody()
    {
        foreach ($this->csvFile as $line) {
            $values = explode(self::DELIMITER, trim(preg_replace('/\s\s+/', ' ', $line)));
            if (count($values) != 22) {
                //Filtre les ligne non compatible
                continue;
            }
            $this->body[] = array_combine($this->header, $values);
        }
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
            $this->beer[] = $beer->getName();
        }
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
        $brewer->setAddress($row[self::HEADER_ADDRESS])
               ->setCity($row[self::HEADER_CITY])
               ->setCoordinate($this->getCoordinate($row[self::HEADER_COORDINATES]))
               ->setCountry($row[self::HEADER_COUNTRY])
               ->setState($row[self::HEADER_STATE])
               ->setWebsite($row[self::HEADER_WEBSITE])
        ;

        $this->created_brewer[$brewer->getName()] = $brewer;
        return $brewer;
    }

    /**
     * @param $coordinate
     * @return Coordinate|null
     */
    private function getCoordinate($coordinate)
    {
        if (empty($coordinate)) {
            return null;
        }
        $explodedData = explode(',', $coordinate);
        if (!is_array($explodedData)) {
            return null;
        }
        return new Coordinate(
            (float)$explodedData[0],
            (float)$explodedData[1]
        );
    }
}
