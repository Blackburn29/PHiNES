<?php

namespace PHiNES\Registers\CPU;

class Registers
{
    const C = 1;
    const Z = 1;
    const I = 1;
    const D = 1;
    const B = 1;
    const U = 1;
    const V = 1;
    const N = 1;

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

    /**
     * Increment the program counter by the specified offset
     * @param int the amount to increment by
     */
    public function incrementPC(int $offset)
    {
        $this->PC = $this->PC + $offset;
    }

    /**
     * Set the registers to the correct values for a rest.
     */
    public function reset()
    {
        $this->A = 0;
        $this->X = 0;
        $this->Y = 0;
        $this->P = self::I;
        $this->SP = 0xFD;
        $this->PC = 0xFFFC;
    }

    /**
     * Dump all registers to a string
     * @return string
     */
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
