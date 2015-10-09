<?php

namespace PHiNES\Bundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunEmu extends Command
{
    protected function configure()
    {
        $this->setName('run')
            ->setDescription('Run the emulator');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}
