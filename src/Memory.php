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

    public function reset()
    {
        foreach ($this->memory as $block) {
            $block = 0xFF;
        }
    }

    public function read($address) {
        return $this->memory[$address];
    }

    public function write($address, $value) {
        if ($value > 0xFFFF) {
            throw new MemoryOverflowException(
                sprintf("Value %X too large! At address %X", $value, $address)
            );
        }
        $this->memory[$address] = $value;
    }

    public static function samePage($a1, $a2)
    {
        return !(($a1 ^ $a2) >> 8);
    }
}
