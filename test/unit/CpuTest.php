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

        $this->assertEquals(0x11, $this->cpu->zeroPageIndex(InstructionSet::ADR_ZPX));

        //Register Y
        $this->cpu->getRegisters()->setY(0x01);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x10);

        $this->assertEquals(0x11, $this->cpu->zeroPageIndex(InstructionSet::ADR_ZPY));
    }

    public function testRelativeAddressingModeReturnsCorrectValue()
    {
        $curr = $this->cpu->getRegisters()->getPC();
        $offset = 0x01;
        $this->cpu->getMemory()->write($curr, $offset);

        $this->assertEquals($curr + $offset + 1, $this->cpu->relative());
    }

    public function testRelativeAddressingModeReturnsCorrectValueWithOffset()
    {
        $curr = $this->cpu->getRegisters()->getPC();
        $offset = 0x80;
        $this->cpu->getMemory()->write($curr, $offset);

        $this->assertEquals($curr + (-(0x100 - $offset)) + 1, $this->cpu->relative());
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
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC() + 1, 0x11);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x10);
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

    public function testAslShiftsAndSetsFlagsCorrectlyWithoutAccumulator()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC() + 1, 0xFF);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x01);
        $this->cpu->getMemory()->write(0xFF01, 0x01);
        $this->cpu->execute(0x0E);
        $this->assertEquals(0x02, $this->cpu->getMemory()->read(0xFF01));
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
        $this->cpu->getRegisters()->setPC(0xFFFB);
        $this->cpu->getRegisters()->setStatusBit(Registers::C, 1);
        $this->cpu->execute(0x18); //CLC
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::C));
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x02);
        $this->cpu->execute(0x90);
        $this->assertEquals(0xFFFF, $this->cpu->getRegisters()->getPC());
    }

    public function testSecSetsCarryAndBcsSetsPCOnCarrySet()
    {
        $this->cpu->getRegisters()->setPC(0xFFFB);
        $this->cpu->getRegisters()->setStatusBit(Registers::C, 0);
        $this->cpu->execute(0x38); //SEC
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::C));
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x02);
        $this->cpu->execute(0xB0);
        $this->assertEquals(0xFFFF, $this->cpu->getRegisters()->getPC());
    }

    public function testBeqSetsPCWhenZeroIsLoadedToRegister()
    {
        $this->cpu->getRegisters()->setPC(0xFFFB);
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
        $this->cpu->getRegisters()->setPC(0xFFFB);
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
        $this->cpu->getRegisters()->setPC(0xFFFB);
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
        $this->cpu->getRegisters()->setPC(0xFFFB);
        $this->cpu->push(0x00);
        $this->cpu->execute(0x28); //PLP
        $this->assertEquals(0x00, $this->cpu->getRegisters()->getP());
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x02);
        $this->cpu->execute(0x50); //BVC
        $this->assertEquals(0xFFFF, $this->cpu->getRegisters()->getPC());
    }

    public function testSetAllStatusBitsWithPlpWillAllowBvsToSetPC()
    {
        $this->cpu->getRegisters()->setPC(0xFFFB);
        $this->cpu->push(0xFF);
        $this->cpu->execute(0x28); //PLP
        $this->assertEquals(0xFF, $this->cpu->getRegisters()->getP());
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x02);
        $this->cpu->execute(0x70); //BVS
        $this->assertEquals(0xFFFF, $this->cpu->getRegisters()->getPC());
    }

    public function testBplSetsPCWhenSignBitIsClear()
    {
        $this->cpu->getRegisters()->setPC(0xFFFB);
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

    public function testCpxWIllSetFlagsCorrectly()
    {
        //Zero
        $this->cpu->getRegisters()->setX(0x00);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x00);
        $this->cpu->execute(0xE0);
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
        //Sign
        $this->cpu->getRegisters()->setX(0x02);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x05);
        $this->cpu->execute(0xE0);
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::N));
        //Carry
        $this->cpu->getRegisters()->setX(0x10);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x05);
        $this->cpu->execute(0xE0);
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::C));
    }

    public function testCpyWIllSetFlagsCorrectly()
    {
        //Zero
        $this->cpu->getRegisters()->setY(0x00);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x00);
        $this->cpu->execute(0xC0);
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
        //Sign
        $this->cpu->getRegisters()->setY(0x02);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x05);
        $this->cpu->execute(0xC0);
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::N));
        //Carry
        $this->cpu->getRegisters()->setY(0x10);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x05);
        $this->cpu->execute(0xC0);
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::C));
    }

    public function testDecWillDecrementValueInMemory()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC() + 1, 0x00);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0xFF);
        $this->cpu->getMemory()->write(0x00FF, 0x0F);
        $this->cpu->execute(0xCE);
        $val = $this->cpu->getMemory()->read(0x00FF);
        $this->assertEquals(0x0E, $val);
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::N));
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
    }

    public function testDexAndDeyDecrementRegistersAccordingly()
    {
        $this->cpu->getRegisters()->setX(0x0F);
        $this->cpu->getRegisters()->setY(0x0F);
        $this->cpu->execute(0xCA);
        $this->cpu->execute(0x88);
        $this->assertEquals(0x0E, $this->cpu->getRegisters()->getX());
        $this->assertEquals(0x0E, $this->cpu->getRegisters()->getY());
    }

    public function testEorWillXORWithAccumulator()
    {
        $this->cpu->getRegisters()->setA(0x81);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x7F);
        $this->cpu->execute(0x49);
        $this->assertEquals(0xFE, $this->cpu->getRegisters()->getA());
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::N));
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
    }

    public function testIncWillIncrementValueInMemory()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC() + 1, 0x00);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0xFF);
        $this->cpu->getMemory()->write(0x00FF, 0x0F);
        $this->cpu->execute(0xEE);
        $val = $this->cpu->getMemory()->read(0x00FF);
        $this->assertEquals(0x10, $val);
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::N));
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
    }

    public function testInxAndInyIncrementRegistersAccordingly()
    {
        $this->cpu->getRegisters()->setX(0x0F);
        $this->cpu->getRegisters()->setY(0x0F);
        $this->cpu->execute(0xE8);
        $this->cpu->execute(0xC8);
        $this->assertEquals(0x10, $this->cpu->getRegisters()->getX());
        $this->assertEquals(0x10, $this->cpu->getRegisters()->getY());
    }

    public function testJmpWillSetPcToCorrectAddress()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0xFA);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC() + 1, 0xCA);
        $this->cpu->getMemory()->write(0xCAFA, 0x01);
        $this->cpu->execute(0x6C);
        $this->assertEquals(0x01, $this->cpu->getRegisters()->getPC());
    }

    public function testJsrWillSetPcToCorrectAddressAndPushPCMinusOneToStack()
    {
        $pc = $this->cpu->getRegisters()->getPC();
        $this->cpu->getMemory()->write($pc, 0xFA);
        $this->cpu->getMemory()->write($pc + 1, 0xCA);
        $this->cpu->execute(0x20);
        $this->assertEquals(0xCAFA, $this->cpu->getRegisters()->getPC());
        $this->assertEquals($pc, $this->cpu->pull16());
    }

    public function testLsrWillShiftBitsCorrectly()
    {
        $this->cpu->getRegisters()->setStatusBit(Registers::C, 1);
        $this->cpu->getRegisters()->setA(0xEE);
        $this->cpu->execute(0x4A);
        $this->assertEquals(0x77, $this->cpu->getRegisters()->getA());
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::C));
    }

    public function testLsrWillShiftBitsCorrectlyFromMemory()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC() + 1, 0xFF);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x01);
        $this->cpu->getMemory()->write(0xFF01, 0xEE);
        $this->cpu->execute(0x4E);
        $this->assertEquals(0x77, $this->cpu->getMemory()->read(0xFF01));
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::C));
    }

    public function testOraLogic()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x00);
        $this->cpu->getRegisters()->setA(0xFF);
        $this->cpu->execute(0x09);
        $this->assertEquals(0xFF, $this->cpu->getRegisters()->getA());
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::N));
    }

    public function testPhaPushesRegAToStackAndPlaWillPullItBackIntoRegister()
    {
        $this->cpu->getRegisters()->setA(0xCA);
        $this->cpu->execute(0x48);
        $this->cpu->getRegisters()->setA(0x00);
        $this->cpu->execute(0x68);
        $this->assertEquals(0xCA, $this->cpu->getRegisters()->getA());
    }

    public function testPhpPushesRegPToStackAndPlpWillPullItBackIntoRegister()
    {
        $this->cpu->getRegisters()->setP(0xCA);
        $this->cpu->execute(0x08);
        $this->cpu->getRegisters()->setP(0x00);
        $this->cpu->execute(0x28);
        $this->assertEquals(0xCA, $this->cpu->getRegisters()->getP());
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

    public function testRotateLeftInstructionRotatesCorrectlyWithMemory()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC() + 1, 0xFF);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x01);
        $this->cpu->getMemory()->write(0xFF01, 0x6E);
        $this->cpu->getRegisters()->setStatusBit(Registers::C, 1);
        $this->cpu->execute(0x2E);
        $this->assertEquals(0xDD, $this->cpu->getMemory()->read(0xFF01));
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

    public function testRotateRightInstructionRotatesCorrectlyWithMemory()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC() + 1, 0xFF);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x01);
        $this->cpu->getMemory()->write(0xFF01, 0x6E);
        $this->cpu->getRegisters()->setStatusBit(Registers::C, 1);
        $this->cpu->execute(0x6E);
        $this->assertEquals(0xB7, $this->cpu->getMemory()->read(0xFF01));
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::C));
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::N));
    }

    public function testRtiWillSetPCCorrectly()
    {
        $this->cpu->push(0x08);
        $this->cpu->push(0x07);
        $this->cpu->push(0x06);
        $this->cpu->execute(0x40);
        $this->assertEquals(0x06, $this->cpu->getRegisters()->getP());
        $this->assertEquals(0x0807, $this->cpu->getRegisters()->getPC());
    }

    public function testRtsWillSetPCCorrectly()
    {
        $this->cpu->push(0x08);
        $this->cpu->push(0x07);
        $this->cpu->execute(0x60);
        $this->assertEquals(0x0808, $this->cpu->getRegisters()->getPC());
    }

    public function testSetCarryAndSubtractGivesCorrectResult()
    {
        $this->cpu->getRegisters()->setPC(0xFFF0);
        $this->cpu->execute(0x38);
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::C));
        $this->cpu->getRegisters()->setA(0x05);
        $this->cpu->getRegisters()->setX(0x05);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC() + 1, 0xFF);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0xF0);
        $this->cpu->getMemory()->write(0xFFF5, 0x05);
        $this->cpu->execute(0xFD); //SBC absInx
        $this->assertEquals(0x00, $this->cpu->getRegisters()->getA());
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
    }

    public function testSedSetsDecimalModeFlag()
    {
        $this->cpu->getRegisters()->setStatusBit(Registers::D, 0);
        $this->cpu->execute(0xF8);
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::D));
    }

    public function testSeiSetsDecimalModeFlag()
    {
        $this->cpu->getRegisters()->setStatusBit(Registers::I, 0);
        $this->cpu->execute(0x78);
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::I));
    }

    public function testStaWillStoreACorrectlyWithIndirectIndex()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x05);
        $this->cpu->getMemory()->write(0x05, 0xF0);
        $this->cpu->getRegisters()->setA(0x05);
        $this->cpu->getRegisters()->setY(0x05);
        $this->cpu->execute(0x91);
        $this->assertEquals(0x05, $this->cpu->getMemory()->read(0xF5));
    }

    public function testStaWillStoreACorrectlyWithIndexIndirect()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0x10);
        $this->cpu->getRegisters()->setX(0x02);
        $this->cpu->getMemory()->write(0x12, 0x05);
        $this->cpu->getRegisters()->setA(0x05);
        $this->cpu->execute(0x81);
        $this->assertEquals(0x05, $this->cpu->getMemory()->read(0x12));
    }

    public function testStxWillStoreXCorrectly()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC() + 1, 0xFF);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0xF0);
        $this->cpu->getRegisters()->setX(0x05);
        $this->cpu->execute(0x8E);
        $this->assertEquals(0x05, $this->cpu->getMemory()->read(0xFFF0));
    }

    public function testStyWillStoreYCorrectly()
    {
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC() + 1, 0xFF);
        $this->cpu->getMemory()->write($this->cpu->getRegisters()->getPC(), 0xF0);
        $this->cpu->getRegisters()->setY(0x05);
        $this->cpu->execute(0x8C);
        $this->assertEquals(0x05, $this->cpu->getMemory()->read(0xFFF0));
    }

    public function testTaxWillTransferValuesAcrossRegisters()
    {
        $this->cpu->getRegisters()->setA(0xFE);
        $this->cpu->getRegisters()->setX(0x00);
        $this->cpu->execute(0xAA);
        $this->assertEquals(0xFE, $this->cpu->getRegisters()->getX());
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::N));
    }

    public function testTayWillTransferValuesAcrossRegisters()
    {
        $this->cpu->getRegisters()->setA(0xFE);
        $this->cpu->getRegisters()->setY(0x00);
        $this->cpu->execute(0xA8);
        $this->assertEquals(0xFE, $this->cpu->getRegisters()->getY());
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::N));
    }

    public function testTsxWillTransferValuesAcrossRegisters()
    {
        $this->cpu->getRegisters()->setSP(0xFE);
        $this->cpu->getRegisters()->setX(0x00);
        $this->cpu->execute(0xBA);
        $this->assertEquals(0xFE, $this->cpu->getRegisters()->getX());
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::N));
    }

    public function testTxaWillTransferValuesAcrossRegisters()
    {
        $this->cpu->getRegisters()->setX(0xFE);
        $this->cpu->getRegisters()->setA(0x00);
        $this->cpu->execute(0x8A);
        $this->assertEquals(0xFE, $this->cpu->getRegisters()->getA());
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::N));
    }

    public function testTxsWillTransferValuesAcrossRegisters()
    {
        $this->cpu->getRegisters()->setX(0xFE);
        $this->cpu->getRegisters()->setSP(0x00);
        $this->cpu->execute(0x9A);
        $this->assertEquals(0xFE, $this->cpu->getRegisters()->getSP());
    }

    public function testTyaWillTransferValuesAcrossRegisters()
    {
        $this->cpu->getRegisters()->setY(0xFE);
        $this->cpu->getRegisters()->setA(0x00);
        $this->cpu->execute(0x98);
        $this->assertEquals(0xFE, $this->cpu->getRegisters()->getA());
        $this->assertNotTrue($this->cpu->getRegisters()->getStatus(Registers::Z));
        $this->assertTrue($this->cpu->getRegisters()->getStatus(Registers::N));
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidOpcodeWillThrowExeption()
    {
        $this->cpu->execute(0xFF);
    }

    protected function setUp()
    {
        $this->cpu = new CPU();
    }
}
