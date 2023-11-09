<?php

/**
 * processor states for out of order pipeline
 */
class StateObjectOoO extends StateObject
{
    /**
     * @var ShadowRegisters shadow register set
     */
    public ShadowRegisters $i_shadow_register;

    /**
     * initializes variables and parent class
     * @param JumpPrediction1 $jmpPred used jump prediction
     * @param ProgramMemory $programMemory program memory of simulation
     */
    public function __construct(JumpPrediction1 $jmpPred, ProgramMemory $programMemory)
    {
        parent::__construct($jmpPred, $programMemory);
        $this->i_shadow_register = new ShadowRegisters();
    }

    /**
     * executes reset of processor state
     * @param int $processID process id of last executed jump
     */
    public function resetOoO(int $processID): void
    {
        parent::reset();
        $this->i_shadow_register->Reset($processID);
    }
}