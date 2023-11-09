<?php

/**
 * contains all needed States as a class
 */
class StateObject
{
    /**
     * @var int current executed tact
     */
    public int $takt;

    /**
     * @var bool holds state if Instruction fetch has to wait for a jump command
     */
    public bool $wait_jump;

    /**
     * @var bool holds state if operand fetch has to wait
     */
    public bool $wait_of;

    /**
     * @var bool holds state if Instruction fetch has to wait
     */
    public bool $wait_if;

    /**
     * @var bool holds information if loop has to be kept
     */
    public bool $hold_loop;

    /**
     * @var int holds current program pointer
     */
    public int $ProgrammPointer;

    /**
     * @var int holds information how many cycles instruction fetch has to wait
     */
    public int $counter_add_shift;

    /**
     * @var array holds information of programm pointer if jump prediction was incorrect
     */
    public array $i_programmPointer_hold;

    /**
     * @var Registers holds simulated registerset
     */
    public Registers $i_registers;

    /**
     * @var Registers holds simulated shadow register set
     */
    public Registers $i_shadow_registers;

    /**
     * @var Memory holds simulated memory
     */
    public Memory $i_memory;

    /**
     * @var JumpPrediction1 holds chosen jump prediction
     */
    public JumpPrediction1 $i_predict;

    /**
     * @var bool defines if pipeline has to be cleared
     */
    public bool $clear_Pipeline;

    /**
     * @var int information which command is last legit command
     */
    public int $clear_processID;

    /**
     * @var array holds information which registers are already read and can be unlocked
     */
    public array $setRegisterReadFree;

    /**
     * @var array holds information which registers are already written and can be unlocked
     */
    public array $setRegisterWriteFree;

    /**
     * @var bool holds information if registers can be unlocked
     */
    public bool $set_registerFree_lock;

    /**
     * @var ProgramMemory holds Program memory
     */
    public ProgramMemory $i_programmMemory;

    /**
     * @var bool defines if pipeline has to be reset
     */
    public bool $reset;

    /**
     * Initalizes with start state
     * @param JumpPrediction1 $jmpPred given jump prediction
     * @param ProgramMemory $programMemory given program memory
     */
    public function __construct(JumpPrediction1 $jmpPred, ProgramMemory $programMemory)
    {
        $this->takt = 0;
        $this->wait_jump = false;
        $this->wait_of = false;
        $this->wait_if = false;
        $this->hold_loop = false;
        $this->ProgrammPointer = 0;
        $this->counter_add_shift = 0;
        $this->i_programmPointer_hold = array();
        $this->i_registers = new Registers();
        $this->i_shadow_registers = new Registers();
        $this->i_memory = new Memory();
        $this->i_predict = $jmpPred;
        $this->clear_Pipeline = false;
        $this->clear_processID = -1;
        $this->set_registerFree_lock = false;
        $this->setRegisterReadFree = array();
        $this->setRegisterWriteFree = array();
        $this->i_programmMemory = $programMemory;
        $this->reset = false;
    }

    /**
     * reset states of processor
     */
    public function reset(): void
    {
        $this->wait_jump = false;
        $this->wait_of = false;
        $this->wait_if = false;
        $this->hold_loop = false;
        $this->counter_add_shift = 0;
        $this->i_programmPointer_hold = array();
        $this->clear_Pipeline = false;
        $this->set_registerFree_lock = false;
        $this->setRegisterReadFree = array();
        $this->reset = true;
    }
}