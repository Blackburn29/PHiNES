<?php

namespace PHiNES\Interrupts\CPU;

use PHiNES\CPU;
use PHiNES\Registers\CPU\Registers;

class Interrupts
{
    const IRQ = 0;
    const NMI = 1;
    const RST = 2;

    public $IRQ = false;
    public $NMI = false;
    public $RST = false;

    public function getInterrupt($interrupt)
    {
        switch($interrupt) {
            case self::IRQ:
                return $this->IRQ;
            case self::NMI:
                return $this->NMI;
            case self::RST:
                return $this->RST;
        }
    }

    public function setInterrupt($interrupt, $val)
    {
        switch($interrupt) {
            case self::IRQ:
                $this->IRQ = $val;
                break;
            case self::NMI:
                $this->NMI = $val;
                break;
            case self::RST:
                $this->RST = $val;
                break;
        }
    }

    /**
     * Maskable interrupt. 
     * Push PC to stack
     * Push P to stack
     * Set I to ignore interrupts
     * Read 16bit interrupt vector located at FFFE-F
     * Place result in PC
     */
    public static function executeIrq(CPU $cpu)
    {
        $cpu->push16($cpu->getRegisters()->getPC());
        $cpu->push($cpu->getRegisters()->getP());
        $cpu->getRegisters()->setStatusBit(Registers::I, 1);
        $addr = $cpu->getMemory()->read16(0xFFFE);
        $cpu->getRegisters()->setPC($addr);
    }

    /**
     * Non-maskable interrupt
     * Same as IRQ except interrupt vector is at FFFA-B
     */
    public static function executeNmi(CPU $cpu)
    {
        $cpu->push16($cpu->getRegisters()->getPC());
        $cpu->push($cpu->getRegisters()->getP());
        $cpu->getRegisters()->setStatusBit(Registers::I, 1);
        $addr = $cpu->getMemory()->read16(0xFFFA);
        $cpu->getRegisters()->setPC($addr);
    }

    /**
     * Reset interrupt.
     * Set PC to initial starting address FFFC
     */
    public static function executeReset(CPU $cpu)
    {
        $addr = $cpu->getMemory()->read16(0xFFFC);
        $cpu->getRegisters()->setPC($addr);
    }
}
