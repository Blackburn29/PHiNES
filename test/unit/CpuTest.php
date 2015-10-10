<?php

namespace PHiNES;

use PHiNES\CPU;

class CpuTest extends \PHPUnit_Framework_TestCase
{
    public function testCpuWillInitializeCorrectly()
    {
        $regs = $this->cpu->getRegisters();
        $this->assertEquals(0, $regs->A);
        $this->assertEquals(0, $regs->X);
        $this->assertEquals(0, $regs->Y);
        $this->assertEquals(0, $regs->X);
        $this->assertEquals(1, $regs->P);
        $this->assertEquals(0xFD, $regs->SP);
        $this->assertEquals(0xFFFC, $regs->PC);

        $ints = $this->cpu->getInterrupts();
        $this->assertEquals(false, $ints->IRQ);
        $this->assertEquals(false, $ints->NMI);
        $this->assertEquals(false, $ints->RST);

        foreach ($this->cpu->getMemory() as $block) {
            $this->assertEquals(0xFF, $block);
        }
    }

    protected function setUp()
    {
        $this->cpu = new CPU();
    }
}
