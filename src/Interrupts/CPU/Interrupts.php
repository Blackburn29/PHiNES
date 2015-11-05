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

}
