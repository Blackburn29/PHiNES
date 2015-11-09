<?php

namespace PHiNES\Instructions\CPU;

use PHiNES\Instructions\CPU\InstructionSet;

class InstructionSetTest extends \PHPUnit_Framework_TestCase
{
    public function testInstructionSetContainsCorrectNumberOfInstructions()
    {
        $instructionSet = InstructionSet::createDefault();
        $this->assertCount(232, $instructionSet->getInstructions());
    }
}
