<?php
/**
 * Emulates the 6502 CPU used in the NES console
 */

namespace PHiNES;

use PHiNES\Registers\PPU as Registers;

final class PPU
{
    private $DEBUG = true;

    private $ppuctrl;
    private $ppumask;
    private $ppustatus;
    private $oamaddr;
    private $oamdata;
    private $ppuscroll;
    private $ppuaddr;
    private $ppudata;
    private $oamdma;

    public function __construct()
    {
        $this->ppuctrl = new Registers\ControlRegister();
        $this->ppumask = new Registers\MaskRegister();
        $this->ppustatus = new Registers\StatusRegister();
        $this->oamaddr = 0;
        $this->oamdata = 0;
        $this->ppuscroll = 0;
        $this->ppuaddr = 0;
        $this->ppudata = 0;
        $this->oamdma = 0;
    }
}
