<?php
/**
 * Generates an instruction set for the 6502
 */

namespace PHiNES\Instructions\CPU;

class InstructionSet
{
    /* Addressing Modes */
    const ADR_IMP = 0; //Implied
    const ADR_ACC = 1; //Accumulator
    const ADR_IMM = 2; //Immediate *
    const ADR_ABS = 3; //Absolute *
    const ADR_ZP = 7; //Zero Page *
    const ADR_REL = 6; //Relative *
    const ADR_ABSX = 4; //Absolute Indexed X
    const ADR_ABSY = 5; //Absolute Indexed Y
    const ADR_ZPX = 8; //Zero Page Indexed X *
    const ADR_ZPY = 9; //Zero Page Indexed Y *
    const ADR_INXINDR = 10; //Indexed Indirect
    const ADR_INDRINX = 11; //Indirect Index
    const ADR_INDR = 12; //Indirect *

    private $instructions = [];

    public static function createDefault()
    {
        $set = new static();

        $set->addInstruction(new Instruction('ADC', 0x69, self::ADR_IMM, 2, 2));
        $set->addInstruction(new Instruction('ADC', 0x65, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('ADC', 0x75, self::ADR_ZPX, 2, 4));
        $set->addInstruction(new Instruction('ADC', 0x6D, self::ADR_ABS, 3, 4));
        $set->addInstruction(new Instruction('ADC', 0x7D, self::ADR_ABSX, 3, 4));
        $set->addInstruction(new Instruction('ADC', 0x79, self::ADR_ABSY, 3, 4));
        $set->addInstruction(new Instruction('ADC', 0x61, self::ADR_INXINDR, 2, 6));
        $set->addInstruction(new Instruction('ADC', 0x71, self::ADR_INDRINX, 2, 5));

        $set->addInstruction(new Instruction('AND', 0x29, self::ADR_IMM, 2, 2));
        $set->addInstruction(new Instruction('AND', 0x25, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('AND', 0x35, self::ADR_ZPX, 2, 4));
        $set->addInstruction(new Instruction('AND', 0x2D, self::ADR_ABS, 3, 4));
        $set->addInstruction(new Instruction('AND', 0x3D, self::ADR_ABSX, 3, 4));
        $set->addInstruction(new Instruction('AND', 0x39, self::ADR_ABSY, 3, 4));
        $set->addInstruction(new Instruction('AND', 0x21, self::ADR_INXINDR, 2, 6));
        $set->addInstruction(new Instruction('AND', 0x31, self::ADR_INDRINX, 2, 5));

        $set->addInstruction(new Instruction('ASL', 0x0A, self::ADR_ACC, 1, 2));
        $set->addInstruction(new Instruction('ASL', 0x06, self::ADR_ZP, 2, 5));
        $set->addInstruction(new Instruction('ASL', 0x16, self::ADR_ZPX, 2, 6));
        $set->addInstruction(new Instruction('ASL', 0x0E, self::ADR_ABS, 3, 6));
        $set->addInstruction(new Instruction('ASL', 0x1E, self::ADR_ABSX, 3, 7));

        $set->addInstruction(new Instruction('BIT', 0x24, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('BIT', 0x2C, self::ADR_ABS, 3, 4));

        $set->addInstruction(new Instruction('BPL', 0x10, self::ADR_REL, 2, 2));
        $set->addInstruction(new Instruction('BMI', 0x30, self::ADR_REL, 2, 2));
        $set->addInstruction(new Instruction('BVC', 0x50, self::ADR_REL, 2, 2));
        $set->addInstruction(new Instruction('BVS', 0x70, self::ADR_REL, 2, 2));
        $set->addInstruction(new Instruction('BCC', 0x90, self::ADR_REL, 2, 2));
        $set->addInstruction(new Instruction('BCS', 0xB0, self::ADR_REL, 2, 2));
        $set->addInstruction(new Instruction('BNE', 0xD0, self::ADR_REL, 2, 2));
        $set->addInstruction(new Instruction('BEQ', 0xF0, self::ADR_REL, 2, 2));

        $set->addInstruction(new Instruction('BRK', 0x00, self::ADR_IMP, 1, 7));

        $set->addInstruction(new Instruction('CMP', 0xC9, self::ADR_IMM, 2, 2));
        $set->addInstruction(new Instruction('CMP', 0xC5, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('CMP', 0xD5, self::ADR_ZPX, 2, 4));
        $set->addInstruction(new Instruction('CMP', 0xCD, self::ADR_ABS, 3, 4));
        $set->addInstruction(new Instruction('CMP', 0xDD, self::ADR_ABSX, 3, 4));
        $set->addInstruction(new Instruction('CMP', 0xD9, self::ADR_ABSY, 3, 4));
        $set->addInstruction(new Instruction('CMP', 0xC1, self::ADR_INXINDR, 2, 6));
        $set->addInstruction(new Instruction('CMP', 0xD1, self::ADR_INDRINX, 2, 5));

        $set->addInstruction(new Instruction('CPX', 0xE0, self::ADR_IMM, 2, 2));
        $set->addInstruction(new Instruction('CPX', 0xE4, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('CPX', 0xEC, self::ADR_ABS, 3, 4));

        $set->addInstruction(new Instruction('CPY', 0xC0, self::ADR_IMM, 2, 2));
        $set->addInstruction(new Instruction('CPY', 0xC4, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('CPY', 0xCC, self::ADR_ABS, 3, 4));

        $set->addInstruction(new Instruction('DEC', 0xC6, self::ADR_ZP, 2, 5));
        $set->addInstruction(new Instruction('DEC', 0xD6, self::ADR_ZPX, 2, 6));
        $set->addInstruction(new Instruction('DEC', 0xCE, self::ADR_ABS, 3, 6));
        $set->addInstruction(new Instruction('DEC', 0xDE, self::ADR_ABSX, 3, 7));

        $set->addInstruction(new Instruction('EOR', 0x49, self::ADR_IMM, 2, 2));
        $set->addInstruction(new Instruction('EOR', 0x45, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('EOR', 0x55, self::ADR_ZPX, 2, 4));
        $set->addInstruction(new Instruction('EOR', 0x4D, self::ADR_ABS, 3, 4));
        $set->addInstruction(new Instruction('EOR', 0x5D, self::ADR_ABSX, 3, 4));
        $set->addInstruction(new Instruction('EOR', 0x59, self::ADR_ABSY, 3, 4));
        $set->addInstruction(new Instruction('EOR', 0x41, self::ADR_INXINDR, 2, 6));
        $set->addInstruction(new Instruction('EOR', 0x51, self::ADR_INDRINX, 2, 5));

        $set->addInstruction(new Instruction('CLC', 0x18, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('SEC', 0x38, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('CLI', 0x58, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('SEI', 0x78, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('CLV', 0xB8, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('CLD', 0xD8, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('SED', 0xF8, self::ADR_IMP, 1, 2));

        $set->addInstruction(new Instruction('INC', 0xE6, self::ADR_ZP, 2, 5));
        $set->addInstruction(new Instruction('INC', 0xF6, self::ADR_ZPX, 2, 6));
        $set->addInstruction(new Instruction('INC', 0xEE, self::ADR_ABS, 3, 6));
        $set->addInstruction(new Instruction('INC', 0xFE, self::ADR_ABSX, 3, 7));

        $set->addInstruction(new Instruction('JMP', 0x4C, self::ADR_ABS, 3, 3));
        $set->addInstruction(new Instruction('JMP', 0x6C, self::ADR_INDR, 3, 5));

        $set->addInstruction(new Instruction('JSR', 0x20, self::ADR_ABS, 3, 6));

        $set->addInstruction(new Instruction('LDA', 0xA9, self::ADR_IMM, 2, 2));
        $set->addInstruction(new Instruction('LDA', 0xA5, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('LDA', 0xB5, self::ADR_ZPX, 2, 4));
        $set->addInstruction(new Instruction('LDA', 0xAD, self::ADR_ABS, 3, 4));
        $set->addInstruction(new Instruction('LDA', 0xBD, self::ADR_ABSX, 3, 4));
        $set->addInstruction(new Instruction('LDA', 0xB9, self::ADR_ABSY, 3, 4));
        $set->addInstruction(new Instruction('LDA', 0xA1, self::ADR_INXINDR, 2, 6));
        $set->addInstruction(new Instruction('LDA', 0xB1, self::ADR_INDRINX, 2, 5));

        $set->addInstruction(new Instruction('LDX', 0xA2, self::ADR_IMM, 2, 2));
        $set->addInstruction(new Instruction('LDX', 0xA6, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('LDX', 0xB6, self::ADR_ZPY, 2, 4));
        $set->addInstruction(new Instruction('LDX', 0xAE, self::ADR_ABS, 3, 4));
        $set->addInstruction(new Instruction('LDX', 0xBE, self::ADR_ABSY, 3, 4));

        $set->addInstruction(new Instruction('LDY', 0xA0, self::ADR_IMM, 2, 2));
        $set->addInstruction(new Instruction('LDY', 0xA4, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('LDY', 0xB4, self::ADR_ZPX, 2, 4));
        $set->addInstruction(new Instruction('LDY', 0xAC, self::ADR_ABS, 3, 4));
        $set->addInstruction(new Instruction('LDY', 0xBC, self::ADR_ABSX, 3, 4));

        $set->addInstruction(new Instruction('LSR', 0x4A, self::ADR_ACC, 1, 2));
        $set->addInstruction(new Instruction('LSR', 0x46, self::ADR_ZP, 2, 5));
        $set->addInstruction(new Instruction('LSR', 0x56, self::ADR_ZPX, 2, 6));
        $set->addInstruction(new Instruction('LSR', 0x4E, self::ADR_ABS, 3, 6));
        $set->addInstruction(new Instruction('LSR', 0x5E, self::ADR_ABSX, 3, 7));

        $set->addInstruction(new Instruction('NOP', 0xEA, self::ADR_IMP, 1, 2));

        $set->addInstruction(new Instruction('ORA', 0x09, self::ADR_IMM, 2, 2));
        $set->addInstruction(new Instruction('ORA', 0x05, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('ORA', 0x15, self::ADR_ZPX, 2, 4));
        $set->addInstruction(new Instruction('ORA', 0x0D, self::ADR_ABS, 3, 4));
        $set->addInstruction(new Instruction('ORA', 0x1D, self::ADR_ABSX, 3, 4));
        $set->addInstruction(new Instruction('ORA', 0x19, self::ADR_ABSY, 3, 4));
        $set->addInstruction(new Instruction('ORA', 0x01, self::ADR_INXINDR, 2, 6));
        $set->addInstruction(new Instruction('ORA', 0x11, self::ADR_INDRINX, 2, 5));

        $set->addInstruction(new Instruction('TAX', 0xAA, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('TXA', 0x8A, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('DEX', 0xCA, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('INX', 0xE8, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('TAY', 0xA8, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('TYA', 0x98, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('DEY', 0x88, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('INY', 0xC8, self::ADR_IMP, 1, 2));

        $set->addInstruction(new Instruction('ROL', 0x2A, self::ADR_ACC, 1, 2));
        $set->addInstruction(new Instruction('ROL', 0x26, self::ADR_ZP, 2, 5));
        $set->addInstruction(new Instruction('ROL', 0x36, self::ADR_ZPX, 2, 6));
        $set->addInstruction(new Instruction('ROL', 0x2E, self::ADR_ABS, 3, 6));
        $set->addInstruction(new Instruction('ROL', 0x3E, self::ADR_ABSX, 3, 7));

        $set->addInstruction(new Instruction('ROR', 0x6A, self::ADR_ACC, 1, 2));
        $set->addInstruction(new Instruction('ROR', 0x66, self::ADR_ZP, 2, 5));
        $set->addInstruction(new Instruction('ROR', 0x76, self::ADR_ZPX, 2, 6));
        $set->addInstruction(new Instruction('ROR', 0x6E, self::ADR_ABS, 3, 6));
        $set->addInstruction(new Instruction('ROR', 0x7E, self::ADR_ABSX, 3, 7));

        $set->addInstruction(new Instruction('RTI', 0x40, self::ADR_IMP, 1, 6));

        $set->addInstruction(new Instruction('RTS', 0x60, self::ADR_IMP, 1, 6));

        $set->addInstruction(new Instruction('SBC', 0xE9, self::ADR_IMM, 2, 2));
        $set->addInstruction(new Instruction('SBC', 0xE5, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('SBC', 0xF5, self::ADR_ZPX, 2, 4));
        $set->addInstruction(new Instruction('SBC', 0xED, self::ADR_ABS, 3, 4));
        $set->addInstruction(new Instruction('SBC', 0xFD, self::ADR_ABSX, 3, 4));
        $set->addInstruction(new Instruction('SBC', 0xF9, self::ADR_ABSY, 3, 4));
        $set->addInstruction(new Instruction('SBC', 0xE1, self::ADR_INXINDR, 2, 6));
        $set->addInstruction(new Instruction('SBC', 0xF1, self::ADR_INDRINX, 2, 5));

        $set->addInstruction(new Instruction('STA', 0x85, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('STA', 0x95, self::ADR_ZPX, 2, 4));
        $set->addInstruction(new Instruction('STA', 0x8D, self::ADR_ABS, 3, 4));
        $set->addInstruction(new Instruction('STA', 0x9D, self::ADR_ABSX, 3, 4));
        $set->addInstruction(new Instruction('STA', 0x99, self::ADR_ABSY, 3, 4));
        $set->addInstruction(new Instruction('STA', 0x81, self::ADR_INXINDR, 2, 6));
        $set->addInstruction(new Instruction('STA', 0x91, self::ADR_INDRINX, 2, 6));

        $set->addInstruction(new Instruction('TXS', 0x9A, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('TSX', 0xBA, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('PHA', 0x48, self::ADR_IMP, 1, 3));
        $set->addInstruction(new Instruction('PLA', 0x68, self::ADR_IMP, 1, 4));
        $set->addInstruction(new Instruction('PHP', 0x08, self::ADR_IMP, 1, 3));
        $set->addInstruction(new Instruction('PLP', 0x28, self::ADR_IMP, 1, 4));

        $set->addInstruction(new Instruction('STX', 0x86, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('STX', 0x96, self::ADR_ZPY, 2, 4));
        $set->addInstruction(new Instruction('STX', 0x8E, self::ADR_ABS, 3, 4));

        $set->addInstruction(new Instruction('STY', 0x84, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('STY', 0x94, self::ADR_ZPX, 2, 4));
        $set->addInstruction(new Instruction('STY', 0x8C, self::ADR_ABS, 3, 4));

        //Unofficial Opcodes
        $set->addInstruction(new Instruction('ALR', 0x4B, self::ADR_IMM, 3, 4));

        //DOP
        $set->addInstruction(new Instruction('*NOP', 0x04, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('*NOP', 0x14, self::ADR_ZPX, 2, 4));
        $set->addInstruction(new Instruction('*NOP', 0x34, self::ADR_ZPX, 2, 4));
        $set->addInstruction(new Instruction('*NOP', 0x44, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('*NOP', 0x54, self::ADR_ZPX, 2, 4));
        $set->addInstruction(new Instruction('*NOP', 0x64, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('*NOP', 0x74, self::ADR_ZPX, 2, 4));
        $set->addInstruction(new Instruction('*NOP', 0x80, self::ADR_IMM, 2, 2));
        $set->addInstruction(new Instruction('*NOP', 0x82, self::ADR_IMM, 2, 2));
        $set->addInstruction(new Instruction('*NOP', 0x89, self::ADR_IMM, 2, 2));
        $set->addInstruction(new Instruction('*NOP', 0xC2, self::ADR_IMM, 2, 2));
        $set->addInstruction(new Instruction('*NOP', 0xD4, self::ADR_ZPX, 2, 4));
        $set->addInstruction(new Instruction('*NOP', 0xE2, self::ADR_IMM, 2, 2));
        $set->addInstruction(new Instruction('*NOP', 0xF4, self::ADR_ZPX, 2, 4));

        //TOP
        $set->addInstruction(new Instruction('*NOP', 0x0C, self::ADR_ABS, 3, 4));
        $set->addInstruction(new Instruction('*NOP', 0x1C, self::ADR_ABSX, 3, 4));
        $set->addInstruction(new Instruction('*NOP', 0x3C, self::ADR_ABSX, 3, 4));
        $set->addInstruction(new Instruction('*NOP', 0x5C, self::ADR_ABSX, 3, 4));
        $set->addInstruction(new Instruction('*NOP', 0x7C, self::ADR_ABSX, 3, 4));
        $set->addInstruction(new Instruction('*NOP', 0xDC, self::ADR_ABSX, 3, 4));
        $set->addInstruction(new Instruction('*NOP', 0xFC, self::ADR_ABSX, 3, 4));
        
        //NOP
        $set->addInstruction(new Instruction('*NOP', 0x1A, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('*NOP', 0x3A, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('*NOP', 0x5A, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('*NOP', 0x7A, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('*NOP', 0xDA, self::ADR_IMP, 1, 2));
        $set->addInstruction(new Instruction('*NOP', 0xFA, self::ADR_IMP, 1, 2));

        //LAX
        $set->addInstruction(new Instruction('*LAX', 0xA7, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('*LAX', 0xB7, self::ADR_ZPY, 2, 4));
        $set->addInstruction(new Instruction('*LAX', 0xAF, self::ADR_ABS, 3, 4));
        $set->addInstruction(new Instruction('*LAX', 0xBF, self::ADR_ABSY, 3, 4));
        $set->addInstruction(new Instruction('*LAX', 0xA3, self::ADR_INXINDR, 2, 6));
        $set->addInstruction(new Instruction('*LAX', 0xB3, self::ADR_INDRINX, 2, 5));

        //SAX
        $set->addInstruction(new Instruction('*SAX', 0x87, self::ADR_ZP, 2, 3));
        $set->addInstruction(new Instruction('*SAX', 0x97, self::ADR_ZPY, 2, 4));
        $set->addInstruction(new Instruction('*SAX', 0x83, self::ADR_INXINDR, 2, 6));
        $set->addInstruction(new Instruction('*SAX', 0x8F, self::ADR_ABS, 3, 4));

        //SBC
        $set->addInstruction(new Instruction('SBC', 0xEB, self::ADR_IMM, 2, 2));

        //DCP
        $set->addInstruction(new Instruction('*DCP', 0xC7, self::ADR_ZP, 2, 5));
        $set->addInstruction(new Instruction('*DCP', 0xD7, self::ADR_ZPX, 2, 6));
        $set->addInstruction(new Instruction('*DCP', 0xCF, self::ADR_ABS, 3, 6));
        $set->addInstruction(new Instruction('*DCP', 0xDF, self::ADR_ABSX, 3, 7));
        $set->addInstruction(new Instruction('*DCP', 0xDB, self::ADR_ABSY, 3, 7));
        $set->addInstruction(new Instruction('*DCP', 0xC3, self::ADR_INXINDR, 2, 8));
        $set->addInstruction(new Instruction('*DCP', 0xD3, self::ADR_INDRINX, 2, 8));

        //ISB
        $set->addInstruction(new Instruction('*ISB', 0xE7, self::ADR_ZP, 2, 5));
        $set->addInstruction(new Instruction('*ISB', 0xF7, self::ADR_ZPX, 2, 6));
        $set->addInstruction(new Instruction('*ISB', 0xEF, self::ADR_ABS, 3, 6));
        $set->addInstruction(new Instruction('*ISB', 0xFF, self::ADR_ABSX, 3, 7));
        $set->addInstruction(new Instruction('*ISB', 0xFB, self::ADR_ABSY, 3, 7));
        $set->addInstruction(new Instruction('*ISB', 0xE3, self::ADR_INXINDR, 2, 8));
        $set->addInstruction(new Instruction('*ISB', 0xF3, self::ADR_INDRINX, 2, 8));

        //SLO
        $set->addInstruction(new Instruction('*SLO', 0x07, self::ADR_ZP, 2, 5));
        $set->addInstruction(new Instruction('*SLO', 0x17, self::ADR_ZPX, 2, 6));
        $set->addInstruction(new Instruction('*SLO', 0x0F, self::ADR_ABS, 3, 6));
        $set->addInstruction(new Instruction('*SLO', 0x1F, self::ADR_ABSX, 3, 7));
        $set->addInstruction(new Instruction('*SLO', 0x1B, self::ADR_ABSY, 3, 7));
        $set->addInstruction(new Instruction('*SLO', 0x03, self::ADR_INXINDR, 2, 8));
        $set->addInstruction(new Instruction('*SLO', 0x13, self::ADR_INDRINX, 2, 8));

        //RLA
        $set->addInstruction(new Instruction('*RLA', 0x27, self::ADR_ZP, 2, 5));
        $set->addInstruction(new Instruction('*RLA', 0x37, self::ADR_ZPX, 2, 6));
        $set->addInstruction(new Instruction('*RLA', 0x2F, self::ADR_ABS, 3, 6));
        $set->addInstruction(new Instruction('*RLA', 0x3F, self::ADR_ABSX, 3, 7));
        $set->addInstruction(new Instruction('*RLA', 0x3B, self::ADR_ABSY, 3, 7));
        $set->addInstruction(new Instruction('*RLA', 0x23, self::ADR_INXINDR, 2, 8));
        $set->addInstruction(new Instruction('*RLA', 0x33, self::ADR_INDRINX, 2, 8));

        //SRE
        $set->addInstruction(new Instruction('*SRE', 0x47, self::ADR_ZP, 2, 5));
        $set->addInstruction(new Instruction('*SRE', 0x57, self::ADR_ZPX, 2, 6));
        $set->addInstruction(new Instruction('*SRE', 0x4F, self::ADR_ABS, 3, 6));
        $set->addInstruction(new Instruction('*SRE', 0x5F, self::ADR_ABSX, 3, 7));
        $set->addInstruction(new Instruction('*SRE', 0x5B, self::ADR_ABSY, 3, 7));
        $set->addInstruction(new Instruction('*SRE', 0x43, self::ADR_INXINDR, 2, 8));
        $set->addInstruction(new Instruction('*SRE', 0x53, self::ADR_INDRINX, 2, 8));

        //RRA
        $set->addInstruction(new Instruction('*RRA', 0x67, self::ADR_ZP, 2, 5));
        $set->addInstruction(new Instruction('*RRA', 0x77, self::ADR_ZPX, 2, 6));
        $set->addInstruction(new Instruction('*RRA', 0x6F, self::ADR_ABS, 3, 6));
        $set->addInstruction(new Instruction('*RRA', 0x7F, self::ADR_ABSX, 3, 7));
        $set->addInstruction(new Instruction('*RRA', 0x7B, self::ADR_ABSY, 3, 7));
        $set->addInstruction(new Instruction('*RRA', 0x63, self::ADR_INXINDR, 2, 8));
        $set->addInstruction(new Instruction('*RRA', 0x73, self::ADR_INDRINX, 2, 8));

        return $set;
    }

    /*
     * Add an instruction to the array, indexed by its opcode.
     * @param Instruction the instruction to save
     */
    private function addInstruction(Instruction $instruction)
    {
        $this->instructions[$instruction->getOpcode()] = $instruction;
    }

    /**
     * Return the instruction set array
     * @return associative array of instructions
     */
    public function getInstructions()
    {
        return $this->instructions;
    }
}
