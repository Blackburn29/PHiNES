<?php
/**
 * Emulates the 6502 CPU used in the NES console
 */

namespace PHiNES;

use PHiNES\Registers\PPU as Registers;

final class PPU
{
    private $DEBUG = true;

    private $cycle = 340;
    private $scanLine = 240;
    private $frame = 0;

    private $ppuctrl;
    private $ppumask;
    private $ppustatus;
    private $oamaddr;
    private $oamdata;
    private $ppuscroll;
    private $ppuaddr;
    private $ppudata;
    private $oamdma;

    private $vram;
    private $sprite;

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

        $this->vram = new Memory(0x8000);
        $this->sprite = new Memory(0x100);
    }

    public function reset()
    {
        $this->cycle = 340;
        $this->scanLine = 240;
        $this->frame = 0;
        $this->vram->reset();
        $this->sprite->reset();
    }
}
