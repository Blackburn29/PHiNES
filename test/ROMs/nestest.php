<?php

function testLoadingROMIntoMemory()
{
    echo "Running rom test!";
    $this->cpu->getRegisters()->setP(0x24);
    $this->cpu->getRegisters()->setSP(0xFD);
    $this->cpu->getRegisters()->setPC(0xC000);
    $this->cpu->getMemory()->load(__DIR__.'/../ROMs/nestest.nes');

    $this->cpu->getMemory()->write(0x4004, 0xFF);
    $this->cpu->getMemory()->write(0x4005, 0xFF);
    $this->cpu->getMemory()->write(0x4006, 0xFF);
    $this->cpu->getMemory()->write(0x4007, 0xFF);
    $this->cpu->getMemory()->write(0x4015, 0xFF);

    $this->cpu->step();
}
