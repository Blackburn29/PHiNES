<?php

namespace PHiNES\Registers\PPU;

use PHiNES\Registers\BaseRegister;

class MaskRegister implements BaseRegister
{
    //Masks
    const g = 0x01; //Grayscale
    const m = 0x02; //Show background leftmost 8 pixels
    const M = 0x04; //Show sprites in leftmost 8 pixels
    const b = 0x08; //Show background
    const s = 0x10; //Show sprites
    const R = 0x20; //Emphasize red (NTSC)
    const G = 0x40; //Emphasize green (NTSC)
    const B = 0x80; //Emphasize blue

    private $register;

    /**
     * {@inheritdoc}
     */
    public function getStatus($mask)
    {
        return !($this->register & $mask) == 0;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatusBit($mask, $value)
    {
        $this->register = $value ? $this->register | $mask : $this->register & (~ $mask);
    }
}

