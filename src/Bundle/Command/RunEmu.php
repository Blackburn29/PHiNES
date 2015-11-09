<?php

namespace PHiNES\Bundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use PHiNES\CPU;

class RunEmu extends Command
{
    protected function configure()
    {
        $this->setName('run')
            ->setDescription('Run the emulator');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cpu = new CPU(true);

        echo "Running rom test!\n";
        $cpu->getRegisters()->setP(0x24);
        $cpu->getRegisters()->setSP(0xFD);
        $cpu->getRegisters()->setPC(0xC000);
        $cpu->getMemory()->load(__DIR__.'/../../../test/ROMs/nestest.nes');

        $cpu->getMemory()->write(0x4004, 0xFF);
        $cpu->getMemory()->write(0x4005, 0xFF);
        $cpu->getMemory()->write(0x4006, 0xFF);
        $cpu->getMemory()->write(0x4007, 0xFF);
        $cpu->getMemory()->write(0x4015, 0xFF);

        $cpu->run();
        $output->writeln("Execution completed successfully!");

        $err1 = $cpu->getMemory()->read(0x0002);
        $err2 = $cpu->getMemory()->read(0x0003);
        if ($err1 != 0x00) {
            $output->writeln(sprintf("There was an error @ 0x0002!  %02X", $err1));
        } else {
            $output->writeln("0x0002 shows no errors!");
        }


        if ($err2 != 0x00) {
            $output->writeln(sprintf("There was an error @ 0x0003   %02X", $err2));
        } else {
            $output->writeln("0x0003 shows no errors!");
        }
    }
}

