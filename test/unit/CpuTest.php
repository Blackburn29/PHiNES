<?php

namespace PHiNES;

use PHiNES\CPU;

class CpuTest extends \PHPUnit_Framework_TestCase
{
    public function testCpuWillInitializeCorrectly()
    {
        $regs = $this->cpu->getRegisters();
        $this->assertEquals(0, $regs->getA());
        $this->assertEquals(0, $regs->getX());
        $this->assertEquals(0, $regs->getY());
        $this->assertEquals(0, $regs->getX());
        $this->assertEquals(0x24, $regs->getP());
        $this->assertEquals(0xFD, $regs->getSP());
        $this->assertEquals(0xFFFC, $regs->getPC());

        $ints = $this->cpu->getInterrupts();
        $this->assertEquals(false, $ints->IRQ);
        $this->assertEquals(false, $ints->NMI);
        $this->assertEquals(false, $ints->RST);

        foreach ($this->cpu->getMemory() as $block) {
            $this->assertEquals(0xFF, $block);
        }
    }

    public function testAddCarryWillAddCorrectly()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x10);
        $this->cpu->getRegisters()->setA(0x10);
        $this->cpu->execute(0x6D);
        $this->assertEquals(0x20, $this->cpu->getRegisters()->getA());
    }

    protected function setUp()
    {
        $this->cpu = new CPU();
    }
}
