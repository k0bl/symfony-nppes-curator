<?php

namespace App\Command;

use App\Entity\City;
use App\Entity\Country;
use App\Entity\State;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Model\CityInterface as CityInterface;

class CitiesImportCommand extends ContainerAwareCommand
{
    protected $io;
    protected $entityClass;
    protected $logger;
    private $iteration = 0;
    protected function configure()
    {
        $this
            ->setName('nppes:cities:import')
            ->setDescription('Import Countries')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'City source file to process.'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        //increase memory limit for this import
        ini_set('memory_limit', '512M');
        
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function get($service)
    {
        return $this->getContainer()->get($service);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getEM();
        $file = $input->getArgument('file');
        $em = $this->getEM();
        $country = $this->findCountry('US');
        if (!file_exists($file) || !is_readable($file)) {
            $this->io->error("The file '$file' does not exist or is not readable.");
            return 1;
        }

        $fp = @fopen($file, 'r');
        if (!$fp) {
            $this->io->error("Unable to open '$file' for reading.");
            return 1;
        }

        $line = fgets($fp); // chomp the first line
        $line = fgetcsv($fp);

        if (!$line) {
            $this->io->error("There are no records in the file '$file'?");
            return 1;
        }

        do {
            ++$this->iteration;
            $cityEntity = new City();
            $state = $this->findState($line[CityInterface::CITY_STATE_CODE], 'US');
            if (!$state) {
                throw new RuntimeException("State not found {$line[CityInterface::CITY_STATE_CODE]}");
            }
            $cityEntity->country = $country;
            $cityEntity->state = $state;
            $cityEntity->name = $line[CityInterface::CITY_NAME];
            $cityEntity->areaCode = $line[CityInterface::CITY_AREA_CODE];
            $cityEntity->zipCodes = $line[CityInterface::CITY_ZIP_CODES];
            $cityEntity->latitude =$line[CityInterface::CITY_LATITUDE];
            $cityEntity->longitude = $line[CityInterface::CITY_LONGITUDE];
            $cityEntity->population = $line[CityInterface::CITY_POPULATION];
            $cityEntity->households = $line[CityInterface::CITY_HOUSEHOLDS];
            $cityEntity->medianIncome = $line[CityInterface::CITY_MEDIAN_INCOME];
            $cityEntity->landArea = $line[CityInterface::CITY_LAND_AREA];
            $cityEntity->waterArea = $line[CityInterface::CITY_WATER_AREA];
            $cityEntity->timeZone = $line[CityInterface::CITY_TIME_ZONE];
            $em->persist($cityEntity);
            $output->write('.');
            if (0 === $this->iteration % 100) {
                $output->write("\n");
            }
            $this->processFlush($output);
            $line = fgetcsv($fp);
        } while ($line);
        $this->prettyFlush($output);
    }

    protected function processFlush($output)
    {
        if (0 === $this->iteration % 1000) {
            $this->prettyFlush($output);
        }
    }

    protected function prettyFlush($output)
    {
        $output->write($this->iteration . " Flushing...\n");
        $this->flush();
    }

    protected function getEM()
    {
        return $this->get('doctrine.orm.entity_manager');
    }
    
    protected function flush()
    {
        $this->get('doctrine.orm.entity_manager')->flush();
    }

    public function findState($abbr, $country)
    {
        $em = $this->getEM();
        return $em->getRepository(State::class)->findByStateAndCountryAbbreviation($abbr, $country);
    }

    public function findCountry($abbr)
    {
        $em = $this->getEM();
        return $em->getRepository(Country::class)->findOneByAbbreviation($abbr);
    }
}
