<?php

namespace App\Command;

use App\Entity\Country;
use App\Entity\Provider;
use App\Entity\State;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CountriesImportCommand extends ContainerAwareCommand
{
    protected $io;
    protected $entityClass;
    protected $logger;

    protected function configure()
    {
        $this
            ->setName('nppes:countries:import')
            ->setDescription('Import Countries')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'Country source file to process.'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
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
        if (!file_exists($file) || !is_readable($file)) {
            $this->io->error("The file '$file' does not exist or is not readable.");
            return 1;
        }
        $countries = json_decode(file_get_contents($file), true);

        foreach ($countries as $country) {
            $countryEntity = new Country();
            $countryEntity->commonName = $country['name']['common'];
            $countryEntity->officialName = $country['name']['official'];
            $countryEntity->abbreviation = $country['cca2'];
            $countryEntity->cca3 = $country['cca3'];
            $countryEntity->ccn3 = $country['ccn3'];
            $em->persist($countryEntity);
        }
        $em->flush();
    }

    protected function getEM()
    {
        return $this->get('doctrine.orm.entity_manager');
    }
}
