<?php

namespace PHiNES\Instructions\CPU;

use PHiNES\Instructions\CPU\InstructionSet;

class InstructionSetTest extends \PHPUnit_Framework_TestCase
{
    public function testInstructionSetContainsCorrectNumberOfInstructions()
    {
        $instructionSet = new InstructionSet();
        $this->assertCount(150, $instructionSet->dump());
    }
}
