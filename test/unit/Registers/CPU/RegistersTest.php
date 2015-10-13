<?php

namespace PHiNES\Registers\CPU;

use PHiNES\Registers\CPU\Registers;

class RegistersTest extends \PHPUnit_Framework_TestCase
{
    public function testPCWillWrapCorrectlyOnOverflow()
    {
        $this->registers->incrementPC(3);//PC starts at OxFFFC
        $this->assertEquals(0xFFFF, $this->registers->getPC());
        //Add 2 to the PC, should result in 0x0001
        $this->registers->incrementPC(2);//PC starts at OxFFFC
        $this->assertEquals(0x0001, $this->registers->getPC());

    }

    //A,X,Y,P,SP
    public function testRegistersWillWrapCorrectlyOnOverflow()
    {
        $this->registers->setA(0xFFA);
        $this->registers->setX(0xFFA);
        $this->registers->setY(0xFFA);
        $this->registers->setP(0xFFA);
        $this->registers->setSP(0xFFA);

        $this->assertEquals(0x0FA, $this->registers->getA());
        $this->assertEquals(0x0FA, $this->registers->getX());
        $this->assertEquals(0x0FA, $this->registers->getY());
        $this->assertEquals(0x0FA, $this->registers->getP());
        $this->assertEquals(0x0FA, $this->registers->getSP());
    }

    public function flags()
    {
        return [
            [Registers::C],
            [Registers::Z],
            [Registers::I],
            [Registers::D],
            [Registers::B],
            [Registers::U],
            [Registers::V],
            [Registers::N],
        ];
    }
    /**
     * @dataProvider flags
     */
    public function testStatusFlagsCanBeRetrievedSuccessfully($bit)
    {
        $this->registers->setP($bit); //Disable all flags except carry
        $this->assertTrue($this->registers->getStatus($bit));
        $this->registers->setP(0x00);
        $this->assertNotTrue($this->registers->getStatus($bit));
    }

    public function testOverflowFlagWillSetCorrectly()
    {
        $this->registers->setP(0x00); //Clear all status registers

        $this->registers->setOverflow(0xAFFFF);
        $this->assertTrue($this->registers->getStatus(Registers::V));

        $this->registers->setOverflow(0x0A);
        $this->assertNotTrue($this->registers->getStatus(Registers::V));
    }

    public function setUp()
    {
        $this->registers = new Registers();
    }
}

