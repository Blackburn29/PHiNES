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

    /* Addressing Modes */
    public function immediate()
    {
        $addr = $this->registers->getPC();
        $this->registers->incrementPC(1);

        return $addr;
    }

    public function zeroPage()
    {
        $value = $this->memory->read($this->registers->getPC());
        $this->registers->incrementPC(1);

        return $value;
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

    public function zeroPageIndex($mode)
    {
        $reg = $this->getRegisterFromAddressingMode($mode);

        $mem = $this->memory->read($this->registers->getPC());
        $this->registers->incrementPC(1);

        return $mem + $reg;
    }

    public function relative()
    {
        $mem = $this->memory->read($this->registers->getPC());

        if ($mem > 0x7F) {
            $offset = -(0x100 - $mem);
        } else {
            $offset = $mem;
        }

        $this->registers->incrementPC(1);
        $value = $this->registers->getPC() + $offset;

    }

    public function absolute()
    {
        $low = $this->memory->read($this->registers->getPC());
        $high = $this->memory->read($this->registers->getPC() + 1);

        $this->registers->incrementPC(2);

        return (($high << 8) & 0xFF) | $low;
    }

    public function indirect()
    {
        $low = $this->memory->read($this->registers->getPC());
        $high = $this->memory->read($this->registers->getPC() + 1);

        $this->registers->incrementPC(2);

        $addressLow = (($high << 8) & 0xFF) | $low;
        $addressHigh = (($high << 8) & 0xFF) | ($low + 1);

        $low = $this->memory->read($addressLow);
        $high = $this->memory->read($addressHigh);

        return (($high << 8) & 0xFF) | $low;
    }

    public function absoluteIndexed($mode)
    {
        $mem = $this->memory->read($this->registers->getPC());
    }

    public function getRegisters()
    {
        return $this->registers;
    }

    public function getInterrupts()
    {
        return $this->interrupts;
    }

    public function getMemory()
    {
        return $this->memory;
    }
}
