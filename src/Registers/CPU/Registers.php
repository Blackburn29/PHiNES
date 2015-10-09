<?php

namespace PHiNES\Registers\CPU;

class Registers
{
    const C, Z, I, D, B, U, V, N = 1;

    public $A;
    public $X;
    public $Y;
    public $P;
    public $SP;
    public $PC;

    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->A = 0;
        $this->X = 0;
        $this->Y = 0;
        $this->P = self::I;
        $this->SP = 0xFD;
        $this->PC = 0xFFFC;
    }

    public function toString()
    {
        return sprintf("A:%02X X:%02X Y:%02X P:%02X SP:%02X",
            $this->A,
            $this->X,
            $this->Y,
            $this->P,
            $this->PC
        );
    }
}
