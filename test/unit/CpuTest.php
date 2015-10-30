<?php

namespace PHiNES;

use PHiNES\CPU;
use PHiNES\Registers\CPU\Registers;
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

    public function testIndirectAddressingModeReturnsCorrectValue()
    {
        //Write 0x1110 to memory as address
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC() + 1, 0x11);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x10);
        //Write 0x04 to that address
        $this->cpu->getMemory()->write(0x1110, 0x04);

        $this->assertEquals(0x04, $this->cpu->indirect());
    }

    public function testAbsoluteIndexedAddressingModeReturnsCorrectValue()
    {
        //Test X
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC() + 1, 0x11);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x10);
        $this->cpu->getRegisters()->setX(0x01);

        $this->assertEquals(0x1111, $this->cpu->absoluteIndexed(InstructionSet::ADR_ZPX));

        //Test Y
        $this->cpu->getRegisters()->setY(0x02);
        $this->assertEquals(0x1112, $this->cpu->absoluteIndexed(InstructionSet::ADR_ZPY));
    }

    public function testIndirectIndexAddressingModeReturnsCorrectValue()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x10);
        $this->cpu->getMemory()->write(0x10, 0x04);

        //Test Y
        $this->cpu->getRegisters()->setY(0x02);
        $this->assertEquals(0x06, $this->cpu->indirectIndex());
    }

    public function testIndexedIndirectAddressingModeReturnsCorrectValue()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x10);
        $this->cpu->getRegisters()->setX(0x02);
        $this->cpu->getMemory()->write(0x12, 0x04);

        $this->assertEquals(0x04, $this->cpu->indexIndirect());
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
        $this->cpu->execute(0x29);
        $this->assertEquals(0x01, $this->cpu->getRegisters()->getA());
    }

    public function testAslShiftsAndSetsFlagsCorrectly()
    {
        $this->cpu->getRegisters()->setA(0x01);
        $this->cpu->execute(0x0A);
        $this->assertEquals(0x02, $this->cpu->getRegisters()->getA());
    }

    public function testBitOperationSetsFlagsCorrectly()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0xFF);
        $this->cpu->getRegisters()->setA(0xFF);
        $this->cpu->execute(0x2C);
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::N));
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::V));
    }

    public function testClcClearsCarryAndBccSetsPCOnCarryClear()
    {
        $this->cpu->getRegisters()->setPC(0xFFFC);
        $this->cpu->getRegisters()->setStatusBit(Registers::C, 1);
        $this->cpu->execute(0x18); //CLC
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::C));
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x02);
        $this->cpu->execute(0x90);
        $this->assertEquals(0xFFFF, $this->cpu->getRegisters()->getPC());
    }

    public function testSecSetsCarryAndBcsSetsPCOnCarrySet()
    {
        $this->cpu->getRegisters()->setPC(0xFFFC);
        $this->cpu->getRegisters()->setStatusBit(Registers::C, 0);
        $this->cpu->execute(0x38); //SEC
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::C));
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x02);
        $this->cpu->execute(0xB0);
        $this->assertEquals(0xFFFF, $this->cpu->getRegisters()->getPC());
    }

    public function testBeqSetsPCWhenZeroIsLoadedToRegister()
    {
        $this->cpu->getRegisters()->setPC(0xFFFC);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x00);
        $this->cpu->getRegisters()->setStatusBit(Registers::Z, 1);
        $this->cpu->execute(0xA9); //LDA
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x02);
        $this->cpu->execute(0xF0);
        $this->assertEquals(0xFFFF, $this->cpu->getRegisters()->getPC());
    }

    public function testBmiSetsPCWhenSignBitIsSet()
    {
        $this->cpu->getRegisters()->setPC(0xFFFC);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0xFF);
        $this->cpu->getRegisters()->setStatusBit(Registers::N, 0);
        $this->cpu->execute(0xA2); //LDX
        $this->assertEquals(0xFF, $this->cpu->getRegisters()->getX());
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::N));
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x02);
        $this->cpu->execute(0x30);
        $this->assertEquals(0xFFFF, $this->cpu->getRegisters()->getPC());
    }

    public function testBneSetsPCWhenNonZeroIsLoadedToRegister()
    {
        $this->cpu->getRegisters()->setPC(0xFFFC);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x01);
        $this->cpu->getRegisters()->setStatusBit(Registers::Z, 0);
        $this->cpu->execute(0xA9); //SEC
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x02);
        $this->cpu->execute(0xD0);
        $this->assertEquals(0xFFFF, $this->cpu->getRegisters()->getPC());
    }

    public function testBrkWillPushToStackAndSetPCFromInterruptVectorCorrectly()
    {
        $this->cpu->getRegisters()->setPC(0xFFFC);
        $this->cpu->getMemory()->write(0xFFFF, 0xDD);
        $this->cpu->getMemory()->write(0xFFFE, 0xEE);
        $this->cpu->execute(0x00); //PC is incremented in this instruction as well
        $this->assertEquals(0xDDEE, $this->cpu->getRegisters()->getPC());
        $this->assertEquals($this->cpu->getRegisters()->getP() | Registers::B | Registers::U , $this->cpu->pull());
        $this->assertEquals(0xFFFE, $this->cpu->pull16());
    }

    public function testClearAllStatusBitsWithPlpWillAllowBvcToSetPC()
    {
        $this->cpu->getRegisters()->setPC(0xFFFC);
        $this->cpu->push(0x00);
        $this->cpu->execute(0x28); //PLP
        $this->assertEquals(0x00, $this->cpu->getRegisters()->getP());
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x02);
        $this->cpu->execute(0x50); //BVC
        $this->assertEquals(0xFFFF, $this->cpu->getRegisters()->getPC());
    }

    public function testSetAllStatusBitsWithPlpWillAllowBvsToSetPC()
    {
        $this->cpu->getRegisters()->setPC(0xFFFC);
        $this->cpu->push(0xFF);
        $this->cpu->execute(0x28); //PLP
        $this->assertEquals(0xFF, $this->cpu->getRegisters()->getP());
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x02);
        $this->cpu->execute(0x70); //BVS
        $this->assertEquals(0xFFFF, $this->cpu->getRegisters()->getPC());
    }

    public function testBplSetsPCWhenSignBitIsClear()
    {
        $this->cpu->getRegisters()->setPC(0xFFFC);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x00);
        $this->cpu->getRegisters()->setStatusBit(Registers::N, 0);
        $this->cpu->execute(0xA4); //LDY
        $this->assertEquals(0x00, $this->cpu->getRegisters()->getY());
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::N));
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x02);
        $this->cpu->execute(0x10);
        $this->assertEquals(0xFFFF, $this->cpu->getRegisters()->getPC());
    }

    public function testCldWillClearDecimalFlag()
    {
        $this->cpu->getRegisters()->setStatusBit(Registers::D, 1);
        $this->cpu->execute(0xD8);
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::D));
    }

    public function testCliWillClearInterruptFlag()
    {
        $this->cpu->getRegisters()->setStatusBit(Registers::I, 1);
        $this->cpu->execute(0x58);
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::I));
    }

    public function testClvWillClearOverflowFlag()
    {
        $this->cpu->getRegisters()->setStatusBit(Registers::V, 1);
        $this->cpu->execute(0xB8);
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::V));
    }

    public function testCmpWillCorrectlyCompareAccumulatorAndMemory()
    {
        $this->cpu->getRegisters()->setA(0x05);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x02);
        $this->cpu->execute(0xC9);
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::C));
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::N));
    }

    public function testRotateLeftInstructionRotatesCorrectly()
    {
        $this->cpu->getRegisters()->setStatusBit(Registers::C, 1);
        $this->cpu->getRegisters()->setA(0x6E);
        $this->cpu->execute(0x2A);
        $this->assertEquals(0xDD, $this->cpu->getRegisters()->getA());
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::C));
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::N));
    }

    public function testRotateRightInstructionRotatesCorrectly()
    {
        $this->cpu->getRegisters()->setStatusBit(Registers::C, 1);
        $this->cpu->getRegisters()->setA(0x6E);
        $this->cpu->execute(0x6A);
        $this->assertEquals(0xB7, $this->cpu->getRegisters()->getA());
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::C));
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::N));
    }

    protected function setUp()
    {
        $this->cpu = new CPU();
    }
}
