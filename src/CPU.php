<?php

namespace PHiNES;

use PHiNES\Registers\CPU\Registers;
use PHiNES\Interrupts\CPU\Interrupts;
use PHiNES\Memory;

class CPU
{
    private $status = 0;
    private $registers;
    private $interrupts;
    private $memory;

    public function __construct()
    {
        $this->registers = new Registers();
        $this->interrupts = new Interrupts();
        $this->memory = new Memory();
    }
}
