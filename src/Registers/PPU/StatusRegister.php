<?php

namespace PHiNES\Registers\PPU;

use PHiNES\Registers\BaseRegister;

class StatusRegister implements BaseRegister
{
    //Masks
    const O = 0x20; //Sprite Overflow
    const S = 0x40; //Sprite 0 hit
    const V = 0x80; //Vertical Blank Start

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


