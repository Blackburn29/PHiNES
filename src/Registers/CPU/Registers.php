<?php
/**
 * The registers for the 6502.
 * Since PHP does not support unsigned integers, we 'create' one by masking
 * off the unused bits.
 */

namespace PHiNES\Registers\CPU;

class Registers
{
    //Status Masks
    const C = 0x01; //Carry
    const Z = 0x02; //Zero
    const I = 0x04;
    const D = 0x08;
    const B = 0x10;
    const U = 0x20;
    const V = 0x40; //Overflow
    const N = 0x60; //Sign

    private $A; //8bit
    private $X; //8bit
    private $Y; //8bit
    private $P; //6bits - status flags
    private $SP; //8bit
    private $PC; //16bit

    public function __construct()
    {
        $this->reset();
    }

    /**
     * Sets the overflow bit if given value is above the 8bit range.
     * @param $value int
     */
    public function setOverflow($value)
    {
        if (0xFF < $value) {
            $this->setStatusBit(self::V, 1);
        } else {
            $this->setStatusBit(self::V, 0);
        }
    }

    /**
     * Sets the carry bit. Used in unsigned arithmetic operations.
     * @param $value int
     */
    public function setCarry($value)
    {
        $temp = $this->A + $value + ($this->getStatus(self::C) ? 1 : 0);
        $this->setStatusBit(self::C, (!(($this->A ^ $value) & 0x80) && (($this->A ^ $temp) & 0x80)));
    }

    /**
     * Sets the sign bit. 1 = negative, 0 = postive
     * @param $value int
     */
    public function setSign($value)
    {
        $this->setStatusBit(self::N, ($value >> 7) & 0x010);
    }

    /**
     * Sets the zero flag if the value given is 0.
     * @param $value int
     */
    public function setZero($value)
    {
        $this->setStatusBit(self::Z, $this->Z = $value == 0 ? 1 : 0);
    }

    /**
     * Returns the status bit specified
     * @param $bit the mask to use for each status bit
     * @return boolean
     */
    public function getStatus($mask)
    {
        return !($this->P & $mask) == 0;
    }

    /**
     * Increment the program counter by the specified offset
     * @param int the amount to increment by
     */
    public function incrementPC($offset)
    {
        $this->PC = ($this->PC + $offset) & 0xFFFF;
    }

    /**
     * Returns the A register.
     * @return int 0x00 - 0xFF
     */
    public function getA()
    {
        return $this->A & 0xFF;
    }

    public function setA($val)
    {
        $this->A = $val & 0xFF;
        $this->A = $this->A & 0xFF;
    }

    /**
     * Returns the X register.
     * @return int 0x00 - 0xFF
     */
    public function getX()
    {
        return $this->X & 0xFF;
    }

    /**
     * Sets the X register to the specified value.
     * @param $val int 0x00 - 0xFF
     */
    public function setX($val)
    {
        $this->X = $val & 0xFF;
    }

    /**
     * Returns the Y register.
     * @return int 0x00 - 0xFF
     */
    public function getY()
    {
        return $this->Y & 0xFF;
    }

    /**
     * Sets the Y register to the specified value.
     * @param $val int 0x00 - 0xFF
     */
    public function setY($val)
    {
        $this->Y = $val & 0xFF;
    }

    /**
     * Returns the status register.
     * @return int 0x00 - 0xFF
     */
    public function getP()
    {
        return $this->P & 0xFF;
    }

    /**
     * Sets the status register to the specified value.
     * @param $val int 0x00 - 0xFF
     */
    public function setP($val)
    {
        $this->P = $val & 0xFF;
    }

    /**
     * Returns the stack pointer
     * @return int 0x00 - 0xFF
     */
    public function getSP()
    {
        return $this->SP & 0xFF;
    }

    /**
     * Sets the stack pointer to the specified value.
     * @param $val int 0x00 - 0xFF
     */
    public function setSP($val)
    {
        $this->SP = $val & 0xFF;
    }

    /**
     * Returns the program counter
     * @return int 0x00 - 0xFFFF
     */
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
        $this->P = 0x24;
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

    private function setStatusBit($mask, $value)
    {
        $this->P = $value ? $this->P | $mask : $this->P & (~ $mask);
    }
}
