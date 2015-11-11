<?php

namespace PHiNES\Registers\PPU;

use PHiNES\Registers\BaseRegister;

class ControlRegister implements BaseRegister
{
    //Masks
    const NN = 0x03; //Nametable Select
    const I = 0x04; //Increment Mode
    const S = 0x08; //Sprite Tile Select
    const B = 0x10; //Background Tile Select
    const H = 0x20; //Sprite Select
    const P = 0x40; //Unused
    const V = 0x80; //Overflow

    private $register;

    /**
     * Returns the status bit specified
     * @param $bit the mask to use for each status bit
     * @return boolean
     */
    public function getStatus($mask)
    {
        return !($this->register & $mask) == 0;
    }

    /**
     * Returns the base nametable address
     *
     * @return int
     */
    public function getNametableAddress()
    {
        switch($this->register & self::NN) {
            case 0:
                return 0x2000;
            case 1:
                return 0x2400;
            case 2:
                return 0x2800;
            case 3:
                return 0x2C00;
        }
    }

    /**
     * Returns the sprite pattern address based on the sprite tile and select registers.
     * Ignore if in 8x16 mode.
     *
     * @return 0x1000 on S set, 0x0000 otherwise 
     */
    public function getSpriteAddress()
    {
        //If in 8x16 mode, ignore the address.
        if ($this->getStatus(self::H)) {
            return 0x0000;
        }

        return $this->getStatus(self::S) ? 0x1000 : 0x0000;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatusBit($mask, $value)
    {
        $this->register = $value ? $this->register | $mask : $this->register & (~ $mask);
    }
}
