<?php
/**
 * A instruction model for the 6502
 */

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

    /**
     * Returns the operation name of the instruction
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the opcode of the instruction
     * @return int 0x00 - 0xFF
     */
    public function getOpcode()
    {
        return $this->opcode;
    }

    /**
     * Returns the addressing mode identifier of the instruction
     * @return int
     */
    public function getAddressingMode()
    {
        return $this->addrMode;
    }
}
