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

    public function setUp()
    {
        $this->registers = new Registers();
    }
}

