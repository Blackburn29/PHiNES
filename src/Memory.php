<?php

namespace PHiNES;

use PHiNES\Exception\MemoryOverflowException;

class Memory
{
    const MEM_SIZE = 65536;
    private $memory;

    public function __construct($size=0)
    {
        $this->memory = new \SplFixedArray($size ? $size : self::MEM_SIZE);
        $this->reset();
    }

    /**
     * Reset all memory to be blank.
     */
    public function reset()
    {
        foreach ($this->memory as $block) {
            $block = 0xFF;
        }
    }

    /**
     * Reads a block of memory at an address and returns the value.
     * @param $address int 0x0000 - 0xFFFF
     * @return int an '8bit' integer
     */
    public function read($address) {
        return $this->memory[$address];
    }

    /**
     * Writes a value to memory at given address.
     * @param $address int 0x0000 - 0xFFFF
     * @param $value int 0x00 - 0xFF
     * @throws MemoryOverflowException if value is too large for block
     */
    public function write($address, $value) {
        if ($value > 0xFFFF) {
            throw new MemoryOverflowException(
                sprintf("Value %X too large! At address %X", $value, $address)
            );
        }

        $this->memory[$address] = $value;
    }

    /**
     * Inspired from nwidger/nintengo. Checks to see if memory value 
     * is on the same page. Used for calculating extra cycles for paging.
     * @param $a1 int 0x0000 - 0xxFFFF
     * @param $a2 int 0x0000 - 0xxFFFF
     * @return boolean
     */
    public static function samePage($a1, $a2)
    {
        return !(($a1 ^ $a2) >> 8);
    }
}
