<?php
/**
 * The registers for the 6502.
 * Since PHP does not support unsigned integers, we 'create' one by masking
 * off the unused bits.
 */

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

    private $A; //8bit
    private $X; //8bit
    private $Y; //8bit
    private $P; //8bit
    private $SP; //8bit
    private $PC; //16bit

    public function __construct()
    {
        $this->reset();
    }

    /**
     * Increment the program counter by the specified offset
     * @param int the amount to increment by
     */
    public function incrementPC($offset)
    {
        $this->PC = ($this->PC + $offset) & 0xFFFF;
    }

    public function getA()
    {
        return $this->A & 0xFF;
    }

    public function setA($val)
    {
        $this->A = $val & 0xFF;
        $this->A = $this->A & 0xFF;
    }

    public function getX()
    {
        return $this->X & 0xFF;
    }

    public function setX($val)
    {
        $this->X = $val & 0xFF;
    }

    public function getY()
    {
        return $this->Y & 0xFF;
    }

    public function setY($val)
    {
        $this->Y = $val & 0xFF;
    }

    public function getP()
    {
        return $this->P & 0xFF;
    }

    public function setP($val)
    {
        $this->P = $val & 0xFF;
    }

    public function getSP()
    {
        return $this->SP & 0xFF;
    }

    public function setSP($val)
    {
        $this->SP = $val & 0xFF;
    }

    public function getPC()
    {
        return $this->PC & 0xFFFF;
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
        return sprintf("A:%X X:%X Y:%X P:%X SP:%X PC:%X",
            $this->A,
            $this->X,
            $this->Y,
            $this->P,
            $this->SP,
            $this->PC
        );
    }
}
