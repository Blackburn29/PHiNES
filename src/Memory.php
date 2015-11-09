<?php
/**
 * Emulates the memory in the NES console.
 */

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
        return $this->memory[$address] & 0xFF;
    }

    /**
     * Reads a 16bit block of memory and returns the value
     * @param @address int
     * @return int '16bit' integer
     */
    public function read16($address)
    {
        $low = $this->read($address);
        $high = $this->read($address + 1);
        return (($high << 8) | $low) & 0xFFFF;
    }
    
    /**
     * Reads a 16bit block of memory with wraparound bug.
     * @param @address int
     * @return int 
     */
    public function read16bug($address)
    {
        $b = ($address + 1) & 0x00FF;
        $low = $this->read($address);
        $high = $this->read($b);
        //printf("b: %04X   low: %02X  high: %02X\n", $b, $low, $high);
        return ($high << 8) | $low;
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

    public function load($file)
    {
        $j = 0xC000;
        $headers = [];
        $data = file_get_contents($file);
        if ($data[0] != 'N' && $data[1] != 'E' && $data[2] != 'S') {
            throw new \Exception('Invalid ROM file');
        }
        $data = unpack('C*', $data);

        for ($i = 1; $i <= sizeof($data); $i++) {
            $byte = $data[$i];
            if ($i <= 16) {
                $headers[] = $byte;
                continue;
            }
            $this->memory[$j] = $byte;
            $j++;

            if ($j === 0x10000) {
                break;
            }
        }
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
