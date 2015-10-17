<?php
/**
 * Emulates the 6502 CPU used in the NES console
 */

namespace PHiNES;

use PHiNES\Instructions\CPU\InstructionSet;
use PHiNES\Registers\CPU\Registers;
use PHiNES\Interrupts\CPU\Interrupts;
use PHiNES\Memory;

class CPU
{
    private $instructions;
    private $registers;
    private $interrupts;
    private $memory;
    private $opMap;

    public function __construct()
    {
        $this->instructions = InstructionSet::createDefault();
        $this->registers = new Registers();
        $this->interrupts = new Interrupts();
        $this->memory = new Memory();
        $this->generateOpMap();
    }

    /**
     * Stores a map of all cpu operations to the operation name
     */
    private function generateOpMap()
    {
        $this->opMap = [
            'ADC' => function($v){$this->adc($v);},
            'AND' => function($v){$this->andA($v);},
        ];
    }

    /**
     * Executes the given opcode
     * @param $opcode int 0x00 - 0xFF
     * @throws \Exception if opcode does not exist
     */
    public function execute($opcode)
    {
        if (isset($this->instructions->getInstructions()[$opcode])) {
            $instruction = $this->instructions->getInstructions()[$opcode];
            $value = $this->getValueFromAddressingMode($instruction->getAddressingMode());
            $this->opMap[$instruction->getName()]($value);
        } else {
            throw new \Exception(sprintf("Invalid opcode %X", $opcode));
        }
    }

    /**
     * Returns a value via the addressing mode used in the opcode
     * @param $mode the addressing mode identifier
     * @return int 0x00 - 0xFF
     */
    private function getValueFromAddressingMode($mode)
    {
        switch($mode) {
            case InstructionSet::ADR_IMP:
                //noop
                return;

            case InstructionSet::ADR_ACC:
                return $this->accumulator();

            case InstructionSet::ADR_IMM:
                return $this->immediate();

            case InstructionSet::ADR_ABS:
                return $this->absolute();

            case InstructionSet::ADR_ZP:
                return $this->zeroPage();

            case InstructionSet::ADR_REL:
                return $this->relative();

            case InstructionSet::ADR_ABSX:
                return $this->absoluteIndexed(InstructionSet::ADR_ZPX);

            case InstructionSet::ADR_ABSY:
                return $this->absoluteIndexed(InstructionSet::ADR_ZPY);

            case InstructionSet::ADR_ZPX:
                return $this->zeroPageIndex(InstructionSet::ADR_ZPX);

            case InstructionSet::ADR_ZPY:
                return $this->zeroPageIndex(InstructionSet::ADR_ZPY);

            case InstructionSet::ADR_INXINDR:
                return $this->indexIndirect();

            case InstructionSet::ADR_INDRINX:
                return $this->indirectIndex();

            case InstructionSet::ADR_INDR:
                return $this->indirect();
        }
    }

    /* Addressing Modes */
    public function accumulator()
    {
        return $this->registers->getA();
    }

    public function immediate()
    {
         return $this->registers->getPC();
    }

    public function zeroPage()
    {
        return $this->memory->read($this->registers->getPC());
    }

    public function zeroPageIndex($mode)
    {
        $reg = $this->getRegisterFromAddressingMode($mode);
        $mem = $this->memory->read($this->registers->getPC());

        return $mem + $reg;
    }

    public function relative()
    {
        $mem = $this->memory->read($this->registers->getPC());
        $offset = $mem;

        if ($mem > 0x7F) {
            $offset = -(0x100 - $mem);
        }

        return $this->registers->getPC() + $offset;

    }

    public function absolute()
    {
        $low = $this->memory->read($this->registers->getPC());
        $high = $this->memory->read($this->registers->getPC() + 1);

        return $high << 8 | $low;
    }

    public function indirect()
    {
        $low = $this->memory->read($this->registers->getPC());
        $high = $this->memory->read($this->registers->getPC() + 1);

        $addressLow = $high << 8 | $low;
        $addressHigh = $high << 8 | ($low + 1);

        $low = $this->memory->read($addressLow);
        $high = $this->memory->read($addressHigh);

        return $high << 8 | $low;
    }

    public function absoluteIndexed($mode)
    {
        $low = $this->memory->read($this->registers->getPC());
        $high = $this->memory->read($this->registers->getPC() + 1);

        $addr = (($high << 8) | $low);
        $result =  $addr + $this->getRegisterFromAddressingMode($mode);

        if (!Memory::samePage($addr, $result)) {
            $this->registers->incrementPC(1);
        }

        return $result;
    }

    public function indirectIndex()
    {
        $value = $this->memory->read($this->registers->getPC());

        $low = $this->memory->read($value);
        $high = $this->memory->read(($value + 1) & 0x00FF);
        $result = (($high << 8) | $low) + $this->registers->getY();

        if (!Memory::samePage($value, $result)) {
            $this->registers->incrementPC(1);
        }

        return $result;
    }

    public function indexIndirect()
    {
        $value = $this->memory->read($this->registers->getPC());
        $adr = ($value + $this->registers->getX()) & 0xFFFF;

        $low = $this->memory->read($adr);
        $high = $this->memory->read(($adr + 1) & 0x00FF);

        return (($high << 8) & 0xFF) | $low;
    }


    /* CPU Operations */
    public function adc($value)
    {
        $value = $this->registers->getA() + $value + ($this->registers->getStatus(Registers::C) ? 1 : 0);
        $this->registers->setOverflow($value);
        $this->registers->setCarry($value);
        $this->registers->setSign($value);
        $this->registers->setZero($value);
        $this->registers->setA($value & 0xFF);

    }

    public function andA($value)
    {
        $this->registers->setA($this->registers->getA() & $value);
    }

    /**
     * Returns the registers assigned to the 6502.
     * @return Registers
     */
    public function getRegisters()
    {
        return $this->registers;
    }

    /**
     * Returns the interrupts assigned to the 6502.
     * @return Interrupts
     */
    public function getInterrupts()
    {
        return $this->interrupts;
    }

    /**
     * Returns the memory assigned to the 6502.
     * @return Memory
     */
    public function getMemory()
    {
        return $this->memory;
    }

    private function getRegisterFromAddressingMode($mode)
    {
        switch($mode) {
            case InstructionSet::ADR_ZPX:
                return $this->registers->getX();
            case InstructionSet::ADR_ZPY:
                return $this->registers->getY();
        }
    }
}
