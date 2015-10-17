<?php

namespace PHiNES;

use PHiNES\CPU;
use PHiNES\Instructions\CPU\InstructionSet;

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

    public function testAccumulatorAddressingModeReturnsCorrectValue()
    {
        $this->cpu->getRegisters()->setA(0x10);
        $this->assertEquals(0x10, $this->cpu->accumulator());
    }

    public function testImmediateAddressingModeReturnsCorrectValue()
    {
        $this->assertEquals($this->cpu->getRegisters()->getPC(), $this->cpu->immediate());
    }

    public function testZeroPageAddressingModeReturnsCorrectValue()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x10);
        $this->assertEquals(0x10, $this->cpu->zeroPage());
    }

    public function testZeroPageIndexAddressingModeReturnsCorrectValue()
    {
        //Register X
        $this->cpu->getRegisters()->setX(0x01);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x10);

        $this->cpu->zeroPageIndex(InstructionSet::ADR_ZPX);
        $this->assertEquals(0x11, $this->cpu->zeroPageIndex(InstructionSet::ADR_ZPX));

        //Register Y
        $this->cpu->getRegisters()->setY(0x01);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x10);

        $this->cpu->zeroPageIndex(InstructionSet::ADR_ZPY);
        $this->assertEquals(0x11, $this->cpu->zeroPageIndex(InstructionSet::ADR_ZPY));
    }

    public function testRelativeAddressingModeReturnsCorrectValue()
    {
        $curr = $this->cpu->getRegisters()->getPC();
        $offset = 0x01;
        $this->cpu->getMemory()->write($curr, $offset);

        $this->assertEquals($curr + $offset, $this->cpu->relative());
    }

    public function testAbsoluteAddressingModeReturnsCorrectValue()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC() + 1, 0x11);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x10);
        $this->assertEquals(0x1110, $this->cpu->absolute());
    }

    public function testAddCarryWillAddCorrectly()
    {
        //Absolute
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x10);
        $this->cpu->getRegisters()->setA(0x10);
        $this->cpu->execute(0x6D);
        $this->assertEquals(0x20, $this->cpu->getRegisters()->getA());
    }

    public function testBitwiseANDOperation()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0xFF);
        $this->cpu->getRegisters()->setA(0x01);
        $this->cpu->execute(0x2D);
        $this->assertEquals(0x01, $this->cpu->getRegisters()->getA());
    }

    protected function setUp()
    {
        $this->cpu = new CPU();
    }
}
