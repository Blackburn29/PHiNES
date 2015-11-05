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

    private $pageFlag = false;

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
            'CLD' => function($v){$this->cld($v);},
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
            'NOP' => function($v){},
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
     * @return int the number of cycles used to execute opcode
     */
    public function execute($opcode)
    {
        $cycles = 0;
        $cycles += $this->watchAndExecuteInterrupts();

        if (isset($this->instructions->getInstructions()[$opcode])) {
            $instruction = $this->instructions->getInstructions()[$opcode];
            $value = $this->getValueFromAddressingMode($instruction->getAddressingMode());

            $cycles+= $instruction->getCycles($this->pageFlag);
            $this->pageFlag = false;

            $this->registers->incrementPC(1);

            //Execute the instruction
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
         $this->registers->incrementPC(1);
    }

    public function zeroPage()
    {
        return $this->memory->read($this->registers->getPC());
        $this->registers->incrementPC(1);
    }

    public function zeroPageIndex($mode)
    {
        $reg = $this->getRegisterFromAddressingMode($mode);
        $mem = $this->memory->read($this->registers->getPC());
        $this->registers->incrementPC(1);

        return $mem + $reg;
    }

    public function relative()
    {
        $mem = $this->memory->read($this->registers->getPC());
        $offset = $mem;

        if ($mem > 0x7F) {
            $offset = -(0x100 - $mem);
        }

        $this->registers->incrementPC(1);
        return $this->registers->getPC() + $offset;
    }

    public function absolute()
    {
        return $this->memory->read16($this->registers->getPC());
        $this->registers->incrementPC(2);
    }

    public function indirect()
    {
        $addr =  $this->memory->read16($this->registers->getPC());
        $this->registers->incrementPC(2);

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
        $this->registers->incrementPC(2);

        if ($this->memory->samePage($addr, $result)) {
            $this->pageFlag = true;
        }

        return $result;
    }

    public function indirectIndex()
    {
        $indr = $this->indirect();
        $result = $indr + $this->registers->getY();
        $this->registers->incrementPC(1);

        if ($this->memory->samePage($indr, $result)) {
            $this->pageFlag = true;
        }

        return $result;
    }

    public function indexIndirect()
    {
        $value = $this->memory->read16($this->registers->getPC());
        $adr = ($value + $this->registers->getX()) & 0xFFFF;
        $this->registers->incrementPC(1);

        $low = $this->memory->read($adr);
        $high = $this->memory->read(($adr + 1) & 0x00FF);

        $result = (($high << 8) & 0xFF) | $low;
        return $result;
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
            $origAddr = $address;
            $address = $this->getMemory()->read($address);
        }

        $shifted = $this->shiftLeft($address);

        if ($mode != InstructionSet::ADR_ACC) {
            $this->getMemory()->write($origAddr, $shifted);
        } else {
            $this->registers->setA($shifted);
        }
    }

    public function bcc($address)
    {
        $this->branch(!$this->registers->getStatus(Registers::C), $address);
    }


    public function bcs($address)
    {
        $this->branch($this->registers->getStatus(Registers::C), $address);
    }

    public function beq($address)
    {
        $this->branch($this->registers->getStatus(Registers::Z), $address);
    }

    public function bit($address) 
    {
        $value = $this->getMemory()->read($address);
        $address = $address & $this->registers->getA();
        $bit6 = ($address & Registers::V) >> 6;
        $bit7 = ($address & Registers::N) >> 7;
        $this->registers->setZero($address);
        $this->registers->setStatusBit(Registers::V, $bit6);
        $this->registers->setStatusBit(Registers::N, $bit7);
    }

    public function bmi($address)
    {
        $this->branch($this->registers->getStatus(Registers::N), $address);
    }

    public function bne($address)
    {
        $this->branch(!$this->registers->getStatus(Registers::Z), $address);
    }

    public function bpl($address)
    {
        $this->branch(!$this->registers->getStatus(Registers::N), $address);
    }

    public function brk($address) 
    {
        $this->registers->incrementPC(1);
        $this->push16($this->registers->getPC());
        $this->push($this->registers->getP() | Registers::B | Registers::U);

        $this->registers->setStatusBit(Registers::I, 1);
        $this->registers->setPC($this->getMemory()->read16(0xFFFE));
    }

    public function bvc($address)
    {
        $this->branch(!$this->registers->getStatus(Registers::V), $address);
    }

    public function bvs($address)
    {
        $this->branch($this->registers->getStatus(Registers::V), $address);
    }

    public function clc($address)
    {
        $this->registers->setStatusBit(Registers::C, 0);
    }

    public function cld($address)
    {
        $this->registers->setStatusBit(Registers::D, 0);
    }

    public function cli($address)
    {
        $this->registers->setStatusBit(Registers::I, 0);
    }

    public function clv($address)
    {
        $this->registers->setStatusBit(Registers::V, 0);
    }

    public function cmp($address)
    {
        $this->compare($this->registers->getA(), $address);
    }

    public function cpx($address)
    {
        $this->compare($this->registers->getX(), $address);
    }

    public function cpy($address)
    {
        $this->compare($this->registers->getY(), $address);
    }

    public function dec($address)
    {
        $value = $this->getMemory()->read($address) - 1;
        $this->getMemory()->write($address, $value & 0xFF);

        $this->registers->setSign($value);
        $this->registers->setZero($value);
    }

    public function dex($address)
    {
        //Implied only
        $value = $this->registers->getX() - 1;
        $this->registers->setX($value);
        $this->registers->setSign($value);
        $this->registers->setZero($value);

    }

    public function dey($address)
    {
        //Implied only
        $value = $this->registers->getY() - 1;
        $this->registers->setY($value);
        $this->registers->setSign($value);
        $this->registers->setZero($value);

    }

    public function eor($address)
    {
        $value = $this->registers->getA() ^ $this->getMemory()->read($address);
        $this->registers->setA($value);
        $this->registers->setSign($value);
        $this->registers->setZero($value);
    }

    public function inc($address)
    {
        $value = $this->getMemory()->read($address) + 1;
        $this->getMemory()->write($address, $value & 0xFF);

        $this->registers->setSign($value);
        $this->registers->setZero($value);
    }

    public function inx($address)
    {
        //Implied only
        $value = $this->registers->getX() + 1;
        $this->registers->setX($value);
        $this->registers->setSign($value);
        $this->registers->setZero($value);

    }

    public function iny($address)
    {
        //Implied only
        $value = $this->registers->getY() + 1;
        $this->registers->setY($value);
        $this->registers->setSign($value);
        $this->registers->setZero($value);

    }

    public function jmp($address)
    {
        $this->registers->setPC($address);
    }

    public function jsr($address)
    {
        $value = $this->registers->getPC() - 1;
        $this->push16($value);
        $this->registers->setPC($address);
    }

    public function lda($address)
    {
        $value = $this->getMemory()->read($address);
        $this->registers->setA($value);
        $this->registers->setSign($value);
        $this->registers->setZero($value);
    }

    public function ldx($address)
    {
        $value = $this->getMemory()->read($address);
        $this->registers->setX($value);
        $this->registers->setSign($value);
        $this->registers->setZero($value);
    }

    public function ldy($address)
    {
        $value = $this->getMemory()->read($address);
        $this->registers->setY($value);
        $this->registers->setSign($value);
        $this->registers->setZero($value);
    }

    public function lsr($address, $mode)
    {
        if ($mode != InstructionSet::ADR_ACC) {
            $origAddr = $address;
            $address = $this->getMemory()->read($address);
        }

        $shifted = $this->shiftRight($address);

        if ($mode != InstructionSet::ADR_ACC) {
            $this->getMemory()->write($origAddr, $shifted);
        } else {
            $this->registers->setA($shifted);
        }
    }

    //NOP - Undefined
    
    public function ora($address)
    {
        $value = $this->registers->getA() | $this->getMemory()->read($address);
        $this->registers->setA($value);
        $this->registers->setSign($value);
        $this->registers->setZero($value);
    }

    public function pha($address)
    {
        $this->push($this->registers->getA());
    }

    public function php($address)
    {
        $this->push($this->registers->getP());
    }

    public function pla($address) 
    {
        $value = $this->pull();
        $this->registers->setZero($value);
        $this->registers->setSign($value);
        $this->registers->setA($value);
    }

    public function plp($address) 
    {
        $value = $this->pull();
        $this->registers->setZero($value);
        $this->registers->setSign($value);
        $this->registers->setP($value);
    }

    public function rol($address, $mode)
    {
        if ($mode != InstructionSet::ADR_ACC) {
            $origAddr = $address;
            $address = $this->getMemory()->read($address);
        }

        $shifted = $this->rotateLeft($address);

        if ($mode != InstructionSet::ADR_ACC) {
            $this->getMemory()->write($origAddr, $shifted);
        } else {
            $this->registers->setA($shifted);
        }

    }

    public function ror($address, $mode)
    {
        if ($mode != InstructionSet::ADR_ACC) {
            $origAddr = $address;
            $address = $this->getMemory()->read($address);
        }

        $shifted = $this->rotateRight($address);

        if ($mode != InstructionSet::ADR_ACC) {
            $this->getMemory()->write($origAddr, $shifted);
        } else {
            $this->registers->setA($shifted);
        }

    }

    public function rti($address)
    {
        $this->registers->setP($this->pull());
        $this->registers->setPC($this->pull16());
    }

    public function rts($address)
    {
        $this->registers->setPC($this->pull16() + 1);
    }

    public function sbc($address)
    {
        $value = $this->registers->getA() - $this->getMemory()->read($address);
        $value = $value - (1 - $this->registers->getStatus(Registers::C));
        $this->registers->setOverflow($value);
        $this->registers->setCarry($value);
        $this->registers->setSign($value);
        $this->registers->setZero($value);
        $this->registers->setA($value);
    }

    public function sec($address)
    {
        $this->registers->setStatusBit(Registers::C, 1);
    }

    public function sed($address)
    {
        $this->registers->setStatusBit(Registers::D, 1);
    }

    public function sei($address)
    {
        $this->registers->setStatusBit(Registers::I, 1);
    }

    public function sta($address)
    {
        $this->getMemory()->write($address, $this->registers->getA());
    }

    public function stx($address)
    {
        $this->getMemory()->write($address, $this->registers->getX());
    }

    public function sty($address)
    {
        $this->getMemory()->write($address, $this->registers->getY());
    }

    public function tax($address)
    {
        $value = $this->registers->getA();
        $this->registers->setSign($value);
        $this->registers->setZero($value);
        $this->registers->setX($value);
    }
    
    public function tay($address)
    {
        $value = $this->registers->getA();
        $this->registers->setSign($value);
        $this->registers->setZero($value);
        $this->registers->setY($value);
    }

    public function tsx($address)
    {
        $value = $this->registers->getSP();
        $this->registers->setSign($value);
        $this->registers->setZero($value);
        $this->registers->setX($value);
    }


    public function txa($address)
    {
        $value = $this->registers->getX();
        $this->registers->setSign($value);
        $this->registers->setZero($value);
        $this->registers->setA($value);
    }

    public function txs($address)
    {
        $value = $this->registers->getX();
        $this->registers->setSign($value);
        $this->registers->setZero($value);
        $this->registers->setSP($value);
    }

    public function tya($address)
    {
        $value = $this->registers->getY();
        $this->registers->setSign($value);
        $this->registers->setZero($value);
        $this->registers->setA($value);
    }

    private function rotateLeft($value)
    {
        $bit7 = $value & Registers::N;
        $shifted = ($value << 1 & 0xFE) | ($this->registers->getP() & Registers::C);
        $this->registers->setStatusBit(Registers::C, $bit7);
        $this->registers->setSign($shifted);
        $this->registers->setZero($shifted);
        return $shifted;
    }

    private function rotateRight($value)
    {
        $bit7 = $value & Registers::C;
        $shifted = ($value >> 1 & 0x7F) | ($this->registers->getStatus(Registers::C) ? 0x80 : 0x00);
        $this->registers->setStatusBit(Registers::C, $bit7);
        $this->registers->setSign($shifted);
        $this->registers->setZero($shifted);
        return $shifted;
    }

    private function compare($register, $address)
    {
        $value = $this->getMemory()->read($address);
        $t = $register - $value;

        $bit7 = ($t & Registers::N) >> 7;
        $this->registers->setStatusBit(Registers::N, $bit7);

        $this->registers->setZero($t);

        if ($this->registers->getA() >= $value) {
            $this->registers->setStatusBit(Registers::C, 1);
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
        $this->getMemory()->write(0x100 | $this->registers->getSP(), $value);
        $this->registers->setSP($this->registers->getSP() - 1);
    }

    public function push16($value)
    {
        $this->push($value >> 8);
        $this->push($value);
    }

    public function pull()
    {
        $this->registers->setSP($this->registers->getSP() + 1);
        return $this->getMemory()->read(0x100 | $this->registers->getSP());
    }

    public function pull16()
    {
        $this->registers->setSP($this->registers->getSP() + 1);
        return $this->getMemory()->read16(0x100 | $this->registers->getSP());

    }

    /**
     * Watch for interrupts and execute them if set
     * @return int the number of cycles used to execute interrupts
     */
    private function watchAndExecuteInterrupts()
    {
        $cycles = 0;

        if (!$this->registers->getStatus(Registers::I)
            && $this->interrupts->getInterrupt(Interrupts::IRQ)) {
            $this->executeIrq();
            $cycles = 7;
        }

        if ($this->interrupts->getInterrupt(Interrupts::NMI)) {
            $this->executeNmi();
            $cycles = 7;
        }

        if ($this->interrupts->getInterrupt(Interrupts::RST)) {
            $this->executeReset();
            $cycles = 7;
        }

        return $cycles;
    }

    /**
     * Maskable interrupt. 
     * Push PC to stack
     * Push P to stack
     * Set I to ignore interrupts
     * Read 16bit interrupt vector located at FFFE-F
     * Place result in PC
     */
    public function executeIrq()
    {
        $this->push16($this->registers->getPC());
        $this->push($this->registers->getP());
        $this->registers->setStatusBit(Registers::I, 1);
        $addr = $this->memory->read16(0xFFFE);
        $this->registers->setPC($addr);
        $this->interrupts->setInterrupt(Interrupts::IRQ, false);
    }

    /**
     * Non-maskable interrupt
     * Same as IRQ except interrupt vector is at FFFA-B
     */
    public function executeNmi()
    {
        $this->push16($this->registers->getPC());
        $this->push($this->registers->getP());
        $this->registers->setStatusBit(Registers::I, 1);
        $addr = $this->memory->read16(0xFFFA);
        $this->registers->setPC($addr);
        $this->interrupts->setInterrupt(Interrupts::NMI, false);
    }

    /**
     * Reset interrupt.
     * Set PC to initial starting address FFFC
     */
    public function executeReset()
    {
        $addr = $this->memory->read16(0xFFFC);
        $this->registers->setPC($addr);
        $this->interrupts->setInterrupt(Interrupts::RST, false);
    }

    private function shiftLeft($value)
    {
        $shifted = $value << 1;
        $this->registers->setCarry($shifted);
        $this->registers->setSign($shifted);
        
        return $shifted;
    }

    private function shiftRight($value)
    {
        $shifted = $value >> 1;
        $this->registers->setCarry($shifted);
        $this->registers->setSign(0);
        $this->registers->setZero($shifted);
        
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

    /**
     * Sets the PC if condition is met
     * @param bool $condition 
     * @param int $address
     */
    private function branch($condition, $address)
    {
        if ($this->memory->samePage($this->registers->getPC(), $address)) {
            $this->pageFlag = true;
        }

        if ($condition) {
            $this->registers->setPC($address);
        }
    }
}
