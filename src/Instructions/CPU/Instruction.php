<?php

namespace PHiNES\Instructions\CPU;

class Instruction
{
    private $name;
    private $opcode;
    private $addrMode;
    private $length;
    private $cycles;

    private function __construct($name, $opcode, $addrMode, $length, $cycles)
    {
        $this->name = $name;
        $this->opcode = $opcode;
        $this->addrMode = $addrMode;
        $this->length = $length;
        $this->cycles = $cycles;
    }
}
