<?php
/**
 * Emulates the 6502 CPU used in the NES console
 */

namespace PHiNES;

use PHiNES\Instructions\CPU\InstructionSet;
use PHiNES\Registers\CPU\Registers;
use PHiNES\Interrupts\CPU\Interrupts;
use PHiNES\Memory;

class CPU
{
    private $instructions;
    private $registers;
    private $interrupts;
    private $memory;
    private $opMap;

    public function __construct()
    {
        $this->instructions = InstructionSet::createDefault();
        $this->registers = new Registers();
        $this->interrupts = new Interrupts();
        $this->memory = new Memory();
        $this->generateOpMap();
    }

    /**
     * Stores a map of all cpu operations to the operation name
     */
    private function generateOpMap()
    {
        $this->opMap = [
            'ADC' => function($v){$this->adc($v);},
            'AND' => function($v){$this->andA($v);},
            'ASL' => function($v, $mode){$this->asl($v, $mode);},
            'BCC' => function($v){$this->bcc($v);},
            'BCS' => function($v){$this->bcs($v);},
            'BEQ' => function($v){$this->beq($v);},
            'BIT' => function($v){$this->bit($v);},
            'BMI' => function($v){$this->bmi($v);},
            'BNE' => function($v){$this->bne($v);},
            'BPL' => function($v){$this->bpl($v);},
            'BRK' => function($v){$this->brk($v);},
            'BVC' => function($v){$this->bvc($v);},
            'BVS' => function($v){$this->bvs($v);},
            'CLC' => function($v){$this->clc($v);},
            'CLI' => function($v){$this->cli($v);},
            'CLV' => function($v){$this->clv($v);},
            'CMP' => function($v){$this->cmp($v);},
            'CPX' => function($v){$this->cpx($v);},
            'CPY' => function($v){$this->cpy($v);},
            'DEC' => function($v){$this->dec($v);},
            'DEX' => function($v){$this->dex($v);},
            'DEY' => function($v){$this->dey($v);},
            'EOR' => function($v){$this->eor($v);},
            'INC' => function($v){$this->inc($v);},
            'INX' => function($v){$this->inx($v);},
            'INY' => function($v){$this->iny($v);},
            'JMP' => function($v){$this->jmp($v);},
            'JSR' => function($v){$this->jsr($v);},
            'LDA' => function($v){$this->lda($v);},
            'LDX' => function($v){$this->ldx($v);},
            'LDY' => function($v){$this->ldy($v);},
            'LSR' => function($v, $mode){$this->lsr($v, $mode);},
            'NOP' => function($v){$this->nop($v);},
            'ORA' => function($v){$this->ora($v);},
            'PHA' => function($v){$this->pha($v);},
            'PHP' => function($v){$this->php($v);},
            'PLA' => function($v){$this->pla($v);},
            'PLP' => function($v){$this->plp($v);},
            'ROL' => function($v, $mode){$this->rol($v, $mode);},
            'ROR' => function($v, $mode){$this->ror($v, $mode);},
            'RTI' => function($v){$this->rti($v);},
            'RTS' => function($v){$this->rts($v);},
            'SBC' => function($v){$this->sbc($v);},
            'SEC' => function($v){$this->sec($v);},
            'SED' => function($v){$this->sed($v);},
            'SEI' => function($v){$this->sei($v);},
            'STA' => function($v){$this->sta($v);},
            'STX' => function($v){$this->stx($v);},
            'STY' => function($v){$this->sty($v);},
            'TAX' => function($v){$this->tax($v);},
            'TAY' => function($v){$this->tay($v);},
            'TSX' => function($v){$this->tsx($v);},
            'TXA' => function($v){$this->txa($v);},
            'TXS' => function($v){$this->txs($v);},
            'TYA' => function($v){$this->tya($v);},
        ];
    }

    /**
     * Executes the given opcode
     * @param $opcode int 0x00 - 0xFF
     * @throws \Exception if opcode does not exist
     */
    public function execute($opcode)
    {
        if (isset($this->instructions->getInstructions()[$opcode])) {
            $instruction = $this->instructions->getInstructions()[$opcode];
            $value = $this->getValueFromAddressingMode($instruction->getAddressingMode());
            $this->opMap[$instruction->getName()]($value, $instruction->getAddressingMode());
        } else {
            throw new \Exception(sprintf("Invalid opcode %X", $opcode));
        }
    }

    /**
     * Returns a value via the addressing mode used in the opcode
     * @param $mode the addressing mode identifier
     * @return int
     */
    private function getValueFromAddressingMode($mode)
    {
        switch($mode) {
            case InstructionSet::ADR_IMP:
                //noop
                return;

            case InstructionSet::ADR_ACC:
                return $this->accumulator();

            case InstructionSet::ADR_IMM:
                return $this->immediate();

            case InstructionSet::ADR_ABS:
                return $this->absolute();

            case InstructionSet::ADR_ZP:
                return $this->zeroPage();

            case InstructionSet::ADR_REL:
                return $this->relative();

            case InstructionSet::ADR_ABSX:
                return $this->absoluteIndexed(InstructionSet::ADR_ZPX);

            case InstructionSet::ADR_ABSY:
                return $this->absoluteIndexed(InstructionSet::ADR_ZPY);

            case InstructionSet::ADR_ZPX:
                return $this->zeroPageIndex(InstructionSet::ADR_ZPX);

            case InstructionSet::ADR_ZPY:
                return $this->zeroPageIndex(InstructionSet::ADR_ZPY);

            case InstructionSet::ADR_INXINDR:
                return $this->indexIndirect();

            case InstructionSet::ADR_INDRINX:
                return $this->indirectIndex();

            case InstructionSet::ADR_INDR:
                return $this->indirect();
        }
    }

    /* Addressing Modes */
    public function accumulator()
    {
        return $this->registers->getA();
    }

    public function immediate()
    {
         return $this->registers->getPC();
    }

    public function zeroPage()
    {
        return $this->memory->read($this->registers->getPC());
    }

    public function zeroPageIndex($mode)
    {
        $reg = $this->getRegisterFromAddressingMode($mode);
        $mem = $this->memory->read($this->registers->getPC());

        return $mem + $reg;
    }

    public function relative()
    {
        $mem = $this->memory->read($this->registers->getPC());
        $offset = $mem;

        if ($mem > 0x7F) {
            $offset = -(0x100 - $mem);
        }

        return $this->registers->getPC() + $offset;
    }

    public function absolute()
    {
        return $this->memory->read16($this->registers->getPC());
    }

    public function indirect()
    {
        $addr =  $this->memory->read16($this->registers->getPC());

        //Handle rollover bug
        $addrRoll = ($addr & 0xFF00) | (($addr & 0xFF) + 1);

        $high = $this->memory->read($addrRoll);
        $low = $this->memory->read($addr);

        return (($high << 8) | $low);
    }

    public function absoluteIndexed($mode)
    {
        $addr = $this->memory->read16($this->registers->getPC());
        $result =  $addr + $this->getRegisterFromAddressingMode($mode);

        return $result;
    }

    public function indirectIndex()
    {
        $indr = $this->indirect();
        $result = $indr + $this->registers->getY();

        return $result;
    }

    public function indexIndirect()
    {
        $value = $this->memory->read16($this->registers->getPC());
        $adr = ($value + $this->registers->getX()) & 0xFFFF;

        $low = $this->memory->read($adr);
        $high = $this->memory->read(($adr + 1) & 0x00FF);

        return (($high << 8) & 0xFF) | $low;
    }


    /* CPU Operations */
    public function adc($address)
    {
        $address = $this->registers->getA() + $address + ($this->registers->getStatus(Registers::C) ? 1 : 0);
        $this->registers->setOverflow($address);
        $this->registers->setCarry($address);
        $this->registers->setSign($address);
        $this->registers->setZero($address);
        $this->registers->setA($address & 0xFF);

    }

    public function andA($address)
    {
        $value = $this->getMemory()->read($address);
        $this->registers->setA($this->registers->getA() & $value);
    }

    public function asl($address, $mode)
    {
        if ($mode != InstructionSet::ADR_ACC) {
            $address = $this->getMemory()->read($address);
        }

        $shifted = $this->shiftLeft($address);

        if ($mode != InstructionSet::ADR_ACC) {
            $this->getMemory()->write($address, $shifted);
        } else {
            $this->getRegisters()->setA($shifted);
        }
    }

    public function bcc($address)
    {
        $value = $this->getMemory()->read($address);
        if (!$this->getRegisters()->getStatus(Registers::C)) {
            $this->getRegisters()->setPC($value);
        }
    }

    public function bcs($address)
    {
        $value = $this->getMemory()->read($address);
        if ($this->getRegisters()->getStatus(Registers::C)) {
            $this->getRegisters()->setPC($value);
        }
    }

    public function beq($address)
    {
        $value = $this->getMemory()->read($address);
        if ($this->getRegisters()->getStatus(Registers::Z)) {
            $this->getRegisters()->setPC($value);
        }
    }

    public function bit($address) 
    {
        $value = $this->getMemory()->read($address);
        $address = $address & $this->getRegisters()->getA();
        $bit6 = ($address & Registers::V) >> 6;
        $bit7 = ($address & Registers::N) >> 7;
        $this->getRegisters()->setZero($address);
        $this->getRegisters()->setStatusBit(Registers::V, $bit6);
        $this->getRegisters()->setStatusBit(Registers::N, $bit7);
    }

    public function bmi($address)
    {
        $value = $this->getMemory()->read($address);
        if ($this->getRegisters()->getStatus(Registers::N)) {
            $this->getRegisters()->setPC($value);
        }
    }

    public function bne($address)
    {
        $value = $this->getMemory()->read($address);
        if (!$this->getRegisters()->getStatus(Registers::Z)) {
            $this->getRegisters()->setPC($value);
        }
    }

    public function bpl($address)
    {
        $value = $this->getMemory()->read($address);
        if (!$this->getRegisters()->getStatus(Registers::N)) {
            $this->getRegisters()->setPC($value);
        }
    }

    public function brk($address) 
    {
        $this->getRegisters()->incrementPC(1);
        $this->push16($this->getRegisters()->getPC());
        $this->push($this->getRegisters()->getP() | Registers::B | Registers::U);

        $this->getRegisters()->setStatusBit(Registers::I, 1);
        $this->getRegisters()->setPC($this->getMemory()->read16(0xFFFE));
    }

    public function bvc($address)
    {
        $value = $this->getMemory()->read($address);
        if (!$this->getRegisters()->getStatus(Registers::V)) {
            $this->getRegisters()->setPC($value);
        }
    }

    public function bvs($address)
    {
        $value = $this->getMemory()->read($address);
        if ($this->getRegisters()->getStatus(Registers::V)) {
            $this->getRegisters()->setPC($value);
        }
    }

    public function clc($address)
    {
        $this->getRegisters()->setStatusBit(Registers::C, 0);
    }

    public function cld($address)
    {
        $this->getRegisters()->setStatusBit(Registers::D, 0);
    }

    public function cli($address)
    {
        $this->getRegisters()->setStatusBit(Registers::I, 0);
    }

    public function clv($address)
    {
        $this->getRegisters()->setStatusBit(Registers::V, 0);
    }

    public function cmp($address)
    {
        $this->compare($this->getRegisters()->getA(), $address);
    }

    public function cpx($address)
    {
        $this->compare($this->getRegisters()->getX(), $address);
    }

    public function cpy($address)
    {
        $this->compare($this->getRegisters()->getY(), $address);
    }

    public function dec($address)
    {
        $value = $this->getMemory()->read($address) - 1;
        $this->getMemory()->write($address, $value & 0xFF);

        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setZero($value);
    }

    public function dex($address)
    {
        //Implied only
        $value = $this->getRegisters()->getX() - 1;
        $this->getRegisters()->setX($value);
        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setZero($value);

    }

    public function dey($address)
    {
        //Implied only
        $value = $this->getRegisters()->getY() - 1;
        $this->getRegisters()->setY($value);
        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setZero($value);

    }

    public function eor($address)
    {
        $value = $this->getRegisters()->getA() ^ $this->getMemory()->read($address);
        $this->getRegisters()->setA($value);
        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setZero($value);
    }

    public function inx($address)
    {
        //Implied only
        $value = $this->getRegisters()->getX() - 1;
        $this->getRegisters()->setX($value);
        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setZero($value);

    }

    public function iny($address)
    {
        //Implied only
        $value = $this->getRegisters()->getY() - 1;
        $this->getRegisters()->setY($value);
        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setZero($value);

    }

    public function jmp($address)
    {
        $this->getRegisters()->setPC($address);
    }

    public function jsr($address)
    {
        $value = $this->getRegisters()->getPC() - 1;
        $this->push16($value);
        $this->getRegisters()->setPC($address);
    }

    public function lda($address)
    {
        $value = $this->getMemory()->read($address);
        $this->getRegisters()->setA($value);
        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setZero($value);
    }

    public function ldx($address)
    {
        $value = $this->getMemory()->read($address);
        $this->getRegisters()->setX($value);
        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setZero($value);
    }

    public function ldy($address)
    {
        $value = $this->getMemory()->read($address);
        $this->getRegisters()->setY($value);
        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setZero($value);
    }

    public function lsr($address, $mode)
    {
        if ($mode != InstructionSet::ADR_ACC) {
            $address = $this->getMemory()->read($address);
        }

        $shifted = $this->shiftRight($address);

        if ($mode != InstructionSet::ADR_ACC) {
            $this->getMemory()->write($address, $shifted);
        } else {
            $this->getRegisters()->setA($shifted);
        }
    }

    //NOP - Undefined
    
    public function ora($address)
    {
        $value = $this->getRegisters()->getA() | $this->getMemory()->read($address);
        $this->getRegisters()->setA($value);
        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setZero($value);
    }

    public function pha($address)
    {
        $this->push($this->getRegisters()->getA());
    }

    public function php($address)
    {
        $this->push($this->getRegisters()->getA());
    }

    public function pla($address) 
    {
        $value = $this->pull();
        $this->getRegisters()->setZero($value);
        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setA($value);
    }

    public function plp($address) 
    {
        $value = $this->pull();
        $this->getRegisters()->setZero($value);
        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setP($value);
    }

    public function rol($address, $mode)
    {
        if ($mode != InstructionSet::ADR_ACC) {
            $address = $this->getMemory()->read($address);
        }

        $shifted = $this->rotateLeft($address);

        if ($mode != InstructionSet::ADR_ACC) {
            $this->getMemory()->write($address, $shifted);
        } else {
            $this->getRegisters()->setA($shifted);
        }

    }

    public function ror($address, $mode)
    {
        if ($mode != InstructionSet::ADR_ACC) {
            $address = $this->getMemory()->read($address);
        }

        $shifted = $this->rotateRight($address);

        if ($mode != InstructionSet::ADR_ACC) {
            $this->getMemory()->write($address, $shifted);
        } else {
            $this->getRegisters()->setA($shifted);
        }

    }

    public function rti($address)
    {
        $this->getRegisters()->setP($this->pull());
        $this->getRegisters()->setPC($this->pull16());
    }

    public function rts($address)
    {
        $this->getRegisters()->setPC($this->pull16() + 1);
    }

    public function sbc($address)
    {
        $value = $this->getRegisters()->getA() - $this->getMemory()->read($address);
        $value = $value - (~$this->getRegisters()->getStatus(Registers::c));
        $this->getRegisters()->setOverflow($value);
        $this->getRegisters()->setCarry($value);
        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setZero($value);
        $this->getRegisters()->setA($value);
    }

    public function sec($address)
    {
        $this->getRegisters()->setStatusBit(Registers::C, 1);
    }

    public function sed($address)
    {
        $this->getRegisters()->setStatusBit(Registers::D, 1);
    }

    public function sei($address)
    {
        $this->getRegisters()->setStatusBit(Registers::I, 1);
    }

    public function sta($address)
    {
        $this->getMemory()->write($address, $this->getRegisters()->getA());
    }

    public function stx($address)
    {
        $this->getMemory()->write($address, $this->getRegisters()->getX());
    }

    public function sty($address)
    {
        $this->getMemory()->write($address, $this->getRegisters()->getY());
    }

    public function tax($address)
    {
        $value = $this->getRegisters()->getA();
        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setZero($value);
        $this->getRegisters()->setX($value);
    }
    
    public function tay($address)
    {
        $value = $this->getRegisters()->getA();
        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setZero($value);
        $this->getRegisters()->setY($value);
    }

    public function tsx($address)
    {
        $value = $this->getRegisters()->getSP();
        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setZero($value);
        $this->getRegisters()->setX($value);
    }


    public function txa($address)
    {
        $value = $this->getRegisters()->getX();
        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setZero($value);
        $this->getRegisters()->setA($value);
    }

    public function txs($address)
    {
        $value = $this->getRegisters()->getX();
        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setZero($value);
        $this->getRegisters()->setSP($value);
    }

    public function tya($address)
    {
        $value = $this->getRegisters()->getY();
        $this->getRegisters()->setSign($value);
        $this->getRegisters()->setZero($value);
        $this->getRegisters()->setA($value);
    }

    private function rotateLeft($value)
    {
        $bit7 = $value & Registers::N;
        $shifted = ($value << 1 & 0xFE) | ($this->getRegisters()->getP() & Registers::C);
        $this->getRegisters()->setStatusBit(Registers::C, $bit7);
        $this->getRegisters()->setSign($shifted);
        $this->getRegisters()->setZero($shifted);
        return $shifted;
    }

    private function rotateRight($value)
    {
        $bit7 = $value & Registers::C;
        $shifted = ($value >> 1 & 0x7F) | ($this->getRegisters()->getStatus(Registers::C) ? 0x80 : 0x00);
        $this->getRegisters()->setStatusBit(Registers::C, $bit7);
        $this->getRegisters()->setSign($shifted);
        $this->getRegisters()->setZero($shifted);
        return $shifted;
    }

    private function compare($register, $address)
    {
        $value = $this->getMemory()->read($address);
        $t = $register - $value;

        $bit7 = ($t & Registers::N) >> 7;
        $this->getRegisters()->setStatusBit(Registers::N, $bit7);

        $this->getRegisters()->setZero($t);

        if ($this->getRegisters()->getA() >= $value) {
            $this->getRegisters()->setStatusBit(Registers::C, 1);
        }
    }

    /**
     * Returns the registers assigned to the 6502.
     * @return Registers
     */
    public function getRegisters()
    {
        return $this->registers;
    }

    /**
     * Returns the interrupts assigned to the 6502.
     * @return Interrupts
     */
    public function getInterrupts()
    {
        return $this->interrupts;
    }

    /**
     * Returns the memory assigned to the 6502.
     * @return Memory
     */
    public function getMemory()
    {
        return $this->memory;
    }

    /**
     * Stack operations
     */
    public function push($value)
    {
        $this->getMemory()->write(0x100 | $this->getRegisters()->getSP(), $value);
    }

    public function push16($value)
    {
        $this->push($value >> 8);
        $this->push($value);
    }

    public function pull()
    {
        $this->getRegisters()->setSP($this->getRegisters()->getSP() + 1);

        return $this->getMemory()->read(0x100 | $this->getRegisters()->getSP());
    }

    public function pull16()
    {
        $this->getRegisters()->setSP($this->getRegisters()->getSP() + 1);

        return $this->getMemory()->read16(0x100 | $this->getRegisters()->getSP());

    }

    private function shiftLeft($value)
    {
        $shifted = $value << 1;
        $this->getRegisters()->setCarry($shifted);
        $this->getRegisters()->setSign($shifted);
        
        return $shifted;
    }

    private function shiftRight($value)
    {
        $shifted = $value >> 1;
        $this->getRegisters()->setCarry($shifted);
        $this->getRegisters()->setSign(0);
        $this->getRegisters()->setZero($shifted);
        
        return $shifted;
    }

    private function getRegisterFromAddressingMode($mode)
    {
        switch($mode) {
            case InstructionSet::ADR_ZPX:
                return $this->registers->getX();
            case InstructionSet::ADR_ZPY:
                return $this->registers->getY();
        }
    }
}
