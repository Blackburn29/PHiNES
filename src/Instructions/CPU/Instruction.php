<?php

namespace PHiNES\Instructions\CPU;

class Instruction
{
    private $name;
    private $opcode;
    private $exec;

    private function __construct($name, $opcode, $exec)
    {
        $this->name = $name;
        $this->opcode = $opcode;
        $this->exec = $exec;
    }
}
