<?php

namespace PHiNES;

use PHiNES\MemoryU;

class MemoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMemoryWillReturnTrueIfBlocksAreOnTheSamePage()
    {
        $this->assertTrue($this->memory->samePage(0x0101, 0x0102));
    }

    public function testMemoryWillReturnFalseIfBlocksAreOnTheSamePage()
    {
        $this->assertNotTrue($this->memory->samePage(0x0101, 0x0201));
    }

    protected function setUp()
    {
        $this->memory = new Memory();
    }
}

