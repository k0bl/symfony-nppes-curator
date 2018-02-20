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

class StatesImportCommand extends ContainerAwareCommand
{
    protected $entityClass;
    protected $logger;

    protected function configure()
    {
        $this
            ->setName('nppes:states:import')
            ->setDescription('Import states')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'State source file to process.'
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
        $states = json_decode(file_get_contents($file));
        $country = $em->getRepository(Country::class)->findOneByAbbreviation('US');
        foreach ($states as $state) {
            $stateEntity = new State();
            $stateEntity->name = $state->name;
            $stateEntity->abbreviation = $state->abbreviation;
            $stateEntity->country = $country;
            $em->persist($stateEntity);
        }
        $em->flush();
    }

    protected function getEM()
    {
        return $this->get('doctrine.orm.entity_manager');
    }
}
