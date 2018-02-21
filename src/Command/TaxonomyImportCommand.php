<?php

namespace App\Command;

use App\Entity\Taxonomy;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Commmon\Collections\ArrayCollection;

use Symfony\Component\Console\Style\SymfonyStyle;

use App\Model\TaxonomyInterface as Tax;

use App\Util\SystemLoggingTrait;


class TaxonomyImportCommand extends ContainerAwareCommand
{
    use SystemLoggingTrait;
    private $itemCount = 0;
    private $iteration = 1;

    private $taxonomymanager = null;

    private $statii = array('C', 'U', 'D', 's');

    protected function configure()
    {
        $this
            ->setName('nppes:taxonomy:import')
            ->setDescription('Import Taxonomy datafile.')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'What to call this Fax Service Taxonomy.'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Perform a dry run, do not change anything'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->dryRun = $input->getOption('dry-run');
        if ($this->dryRun) {
            $this->logger->warning('Performing a dry run');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $col = new ArrayCollection();
        $output->writeln($col->count());
        


        $this->taxonomymanager = $this->get('doctrine.orm.entity_manager');

        $this->io->title('Create Taxonomy');
        $file = $input->getArgument('file');
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
            throw new \RuntimeException("There are no records in the file '$file'?");
        }

        do {
            $numiteration = $this->iteration*1000;

            $out = $line[Tax::TAXONOMY_CODE] . ': ';

            if ($this->itemCount > $numiteration)
            {
                $this->iteration++;
                $this->taxonomymanager->flush();

                $out .= "\n";
                $out .= "Incremented the interation: "+$numiteration;
                $out .= "\n";

                $this->io->text($out);
            }

            $this->itemCount++;

            $taxonomy = new Taxonomy();

            $taxonomy->code = $line[Tax::TAXONOMY_CODE];
            $taxonomy->grouping = $line[Tax::TAXONOMY_GROUPING];
            $taxonomy->classification = $line[Tax::TAXONOMY_CLASSIFICATION];
            $taxonomy->specialization = $line[Tax::TAXONOMY_SPECIALIZATION];
            $taxonomy->definition = $line[Tax::TAXONOMY_DEFINITION];

            $this->taxonomymanager->persist($taxonomy);

            $taxonomyName = $line[Tax::TAXONOMY_CODE];

            $out .= $taxonomyName.": HAS BEEN ADDED TO THE DATABASE - ITEM #: ".$this->itemCount."\n";

            $this->reportIteration(
                $this->statii[array_rand($this->statii)], // Stopgap to determine record status
                $output
            );
            $line = fgetcsv($fp);
        } while ($line);

        $this->taxonomymanager->flush();


        fclose($fp);
    }

    protected function reportIteration($status, OutputInterface $output)
    {
        if ($output->getVerbosity() !== OutputInterface::VERBOSITY_NORMAL) {
            return;
        }

        $zeroCount = $this->itemCount - 1;
        if (49 === $zeroCount % 50) {
            $status = "$status\n";
        } elseif (0 === $zeroCount % 10) {
            $status = " $status";
        }
        $output->write($status);
    }

    protected function flush()
    {
        if (!$this->dryRun) {
            $this->get('doctrine.orm.entity_manager')->flush();
        }
    }

    protected function get($service)
    {
        return $this->getContainer()->get($service);
    }
}
