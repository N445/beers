<?php

namespace App\Command;

use App\Kernel;
use App\Repository\Beer\BrewerRepository;
use App\Repository\Beer\CategoryRepository;
use App\Repository\Beer\StyleRepository;
use App\Repository\BeerRepository;
use Symfony\Component\Console\Command\Command;
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

    private $body;

    public function __construct($name = null,
                                KernelInterface $kernel,
                                Filesystem $filesystem)
    {
        $this->kernel      = $kernel;
        $this->project_dir = $kernel->getProjectDir();
        $this->filesystem  = $filesystem;
        $this->filepath    = $this->project_dir . self::FILE_DIR . self::FILENAME;
        $this->body        = [];
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

        $this->csvFile = file($this->filepath);
        $this->setHeader();
        $this->setBody();
        dump($this->header);
        dump($this->body);

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
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
}
