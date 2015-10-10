<?php

namespace PHiNES\Instructions\CPU;

class Instruction
{
    private $name;
    private $opcode;
    private $addrMode;
    private $length;
    private $cycles;

    public function __construct($name, $opcode, $addrMode, $length, $cycles)
    {
        $this->name = $name;
        $this->opcode = $opcode;
        $this->addrMode = $addrMode;
        $this->length = $length;
        $this->cycles = $cycles;
    }

    public function getOpcode()
    {
        return $this->opcode;
    }
}
