<?php

namespace PHiNES\Registers;

interface BaseRegister
{
    public function setStatusBit($mask, $value);

    /**
     * Returns the status bit specified
     * @param $bit the mask to use for each status bit
     * @return boolean
     */
    public function getStatus($mask);

}

