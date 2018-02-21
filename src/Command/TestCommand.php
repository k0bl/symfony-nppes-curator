<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Common\Collections\ArrayCollection;

class TestCommand extends Command
{
    protected function configure()
    {
        $this->setName('app:test');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
	$col = new ArrayCollection();
	$output->writeln($col->count());	
    }
}
