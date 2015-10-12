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

    public function testMemoryCanBeWrittenToAndReadFrom()
    {
        $this->memory->write(0x0101, 0x04);
        $this->assertEquals(0x04, $this->memory->read(0x0101));
    }

    /**
     * @expectedException PHiNES\Exception\MemoryOverflowException
     */
    public function testMemoryWillThrowExceptionIfValueIsTooLargeForBlock()
    {
        $this->memory->write(0x04, 0xAD111);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testMemoryWillThrowExceptionIfAddressIsOutOfRange()
    {
        $this->memory->write(0xAD111, 0x00);
    }

    protected function setUp()
    {
        $this->memory = new Memory();
    }
}

