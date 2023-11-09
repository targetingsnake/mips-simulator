<?php

/**
 * Superskalar out of order part pipeline
 */
class PipelineSuperskalarExecOoO extends ControllerMain
{
    /**
     * @var Command Buffer between instruction pool and execution
     */
    private Command $Stage_ip_ex;

    /**
     * @var ReorderBufferCommand Buffer between execution and reoder stage
     */
    private ReorderBufferCommand $Stage_ex_ro;

    /**
     * @var bool Lock for buffer between instruction pool and execution
     */
    private bool $Stage_ip_ex_lock;

    /**
     * @var bool Lock for buffer between execution and reoder stage
     */
    private bool $Stage_ex_ro_lock;

    /**
     * @var int defines which jump prediction is used
     */
    private int $jump_prediction;

    /**
     * @var StateObjectOoO holds state of processor
     */
    public StateObjectOoO $state;

    /**
     * initalizes variables and parent class
     * @param string $programmCode program code which will be simulated
     * @param int $jmpPred defines which jump prediction is used
     */
    public function __construct(string $programmCode, int $jmpPred)
    {
        parent::__construct($programmCode);
        $this->jump_prediction = $jmpPred;
        $this->Stage_ex_ro_lock = false;
        $this->Stage_ip_ex_lock = false;
        $this->Stage_ex_ro = new ReorderBufferCommand(new Register("\$zero"), new Command("", 0), false);
        $this->Stage_ip_ex = new Command("", 0);
    }

    /**
     * checks if part pipeline is busy
     * @return bool true if part pipeline is busy
     */
    public function isBusy(): bool
    {
        return $this->Stage_ip_ex_lock;
    }

    /**
     * loads next command into part pipeline
     * @param Command $cmd next command
     */
    public function loadNextCommand(Command $cmd): void
    {
        $this->Stage_ip_ex = $cmd;
        $this->Stage_ip_ex_lock = true;
    }

    /**
     * returns last executed command and its result
     * @return ReorderBufferCommand last executed command and result
     */
    public function getResult(): ReorderBufferCommand
    {
        $this->Stage_ex_ro_lock = false;
        return $this->Stage_ex_ro;
    }

    /**
     * checks if part pipeline has a result
     * @return bool true if there is a result
     */
    public function isReadable() : bool {
        return $this->Stage_ex_ro_lock;
    }

    /**
     * checks if pipeline can perform a cycle
     * @return bool true if pipeline is executable
     */
    public function isRunnable() :bool {
        return $this->Stage_ip_ex_lock || $this->Stage_ex_ro_lock;
    }

    /**
     * represents execution step of cpu pipeline
     */
    protected function execute_command(): void
    {
        if ($this->Stage_ex_ro_lock) {
            $this->Stage_ex_ro->setBasicCommand($this->i_execution->processCmd($this->Stage_ex_ro->getBasicCommand(), $this->state->takt));
            return;
        }
        if ($this->Stage_ip_ex->getExecutionTimeRemaining() == 1) {
            $this->i_memory = $this->state->i_memory;
            $result = $this->execute($this->Stage_ip_ex, $this->state->ProgrammPointer, $this->state->i_shadow_register->getRegisters());
            if ($result['jmp'] && $this->jump_prediction == 0) {
                $this->state->counter_add_shift = $this->state->counter_add_shift + 2;
            }
            if (($result['cmd']->getBasicCommand()->getCommandType() == 5 || $result['cmd']->getBasicCommand()->getCommandType() == 6 || $result['cmd']->getBasicCommand()->getCommandType() == 1)) {
                $this->state->wait_jump = false;
                if ($this->jump_prediction == 0) {
                    if (!$result['jmp']) {
                        $this->state->counter_add_shift = $this->state->counter_add_shift + 2;
                    }
                }
            }
            if ($this->jump_prediction > 0) {
                if (($result['cmd']->getBasicCommand()->getCommandType() == 5 || $result['cmd']->getBasicCommand()->getCommandType() == 6 || $result['cmd']->getBasicCommand()->getCommandType() == 1)) {
                    $this->state->i_predict->addPrediction($result['cmd']->getBasicCommand()->getId(), $result['jmp']);
                    if ($result['cmd']->getBasicCommand()->getPrediction() != $result['jmp']) {
                        $this->state->clear_Pipeline = true;
                        $this->state->clear_processID = $result['cmd']->getBasicCommand()->getProcessId();
                        if (!$result['jmp']) {
                            $this->state->ProgrammPointer = $this->state->i_programmPointer_hold[$result['cmd']->getBasicCommand()->getProcessId()];
                            unset($this->state->i_programmPointer_hold[$result['cmd']->getBasicCommand()->getProcessId()]);
                        } else {
                            $this->state->ProgrammPointer = $result['pgm'];
                        }
                    }
                }
            }
            $this->Stage_ex_ro_lock = true;
            $this->Stage_ex_ro = $result['cmd'];
            $this->Stage_ex_ro->setBasicCommand($this->i_execution->processCmd($this->Stage_ex_ro->getBasicCommand(), $this->state->takt));
            if ($this->jump_prediction == 0) {
                $this->state->ProgrammPointer = $result['pgm'];
            }
            $this->Stage_ip_ex_lock = false;
            $this->state->i_memory = $this->i_memory;
        } else {
            $this->Stage_ip_ex->decreaseExecutionTime();
            $this->Stage_ip_ex = $this->i_execution->processCmd($this->Stage_ip_ex, $this->state->takt);
        }
    }

    /**
     * executes a cycle
     * @param StateObjectOoO $state current state of processor
     * @return StateObjectOoO state after a cycle
     */
    public function do_takt(StateObjectOoO $state) : StateObjectOoO
    {
        $this->state = $state;

        /**
         * execute operand fetch
         */
        if ($this->Stage_ip_ex_lock) {
            $this->execute_command();
        }

        return $this->state;
    }

    /**
     * executes a command
     * @param Command $cmd command to be executed
     * @param int $programmPointer current Program pointer
     * @param Registers|null $registers registers optional
     * @return array command after execution ["cmd"], next programm pointer ["pgm"] adn if a jump was executed ["jmp"]
     */
    protected function execute(Command $cmd, int $programmPointer, Registers $registers = null): array
    {
        if (is_null($registers)){
            $registers = $this->i_registers;
        }
        $jmp = false;
        $register_res = new Register("\$zero");
        $register_set = false;
        if ($cmd->getCommandType() == 0) { //Type I
            $register = $registers->getRegisterContent($cmd->getArguments()[2]);
            $result = $this->i_execution->ExecuteTypeI($cmd, $register->getValue());
            $register_res = $result;
            $register_set = true;
        } else if ($cmd->getCommandType() == 1) { // Type J
            $programmPointer = $this->i_programmMemory->getLabel($this->i_execution->ExecuteTypeJ($cmd));
            $jmp = true;
            $register_set = false;
        } else if ($cmd->getCommandType() == 2) { // Type R
            $register1 = $registers->getRegisterContent($cmd->getArguments()[2]);
            $register2 = $registers->getRegisterContent($cmd->getArguments()[3]);
            $result = $this->i_execution->ExecuteTypeR($cmd, $register1->getValue(), $register2->getValue());
            $register_res = $result;
            $register_set = true;
        } else if ($cmd->getCommandType() == 3) { // Type Memory Command
            $memoryCellAddr = intval($cmd->getArguments()[2]);
            switch ($cmd->getCommand()) {
                case 'lw':
                    $register = new Register($cmd->getArguments()[1]);
                    $register->setLockState(false);
                    $register->setValue($this->i_memory->getValue($memoryCellAddr));
                    $register_res = $register;
                    $register_set = true;
                    break;
                case 'sw':
                    $register = $registers->getRegisterContent($cmd->getArguments()[1])->getValue();
                    $this->i_memory->setValue($memoryCellAddr, $register);
                    $register_set = false;
                    break;
            }
        } else if ($cmd->getCommandType() == 5) { // Type 2 parameters Jump
            $register = $registers->getRegisterContent($cmd->getArguments()[1])->getValue();
            $result = $this->i_execution->ExecuteJump2Params($cmd, $register);
            $register_set = false;
            if ($result['jump']) {
                $programmPointer = $this->i_programmMemory->getLabel($result['label']);
                $jmp = true;
            }
        } else if ($cmd->getCommandType() == 6) { // Type 3 parameters Jump
            $register1 = $registers->getRegisterContent($cmd->getArguments()[1])->getValue();
            $register2 = $registers->getRegisterContent($cmd->getArguments()[2])->getValue();
            $result = $this->i_execution->ExecuteJump3Params($cmd, $register1, $register2);
            $register_set = false;
            if ($result['jump']) {
                $programmPointer = $this->i_programmMemory->getLabel($result['label']);
                $jmp = true;
            }
        }
        $cmd = new ReorderBufferCommand($register_res, $cmd, $register_set);
        return array(
            "cmd" => $cmd,
            "pgm" => $programmPointer,
            "jmp" => $jmp
        );
    }

    /**
     * resets part pipeline
     * @param int $pID identifier of last executed jump command
     * @return ResetPipelineResult result of part pipeline for log
     */
    public function reset(int $pID) : ResetPipelineResult {
        $result = new ResetPipelineResult();
        if ($this->Stage_ip_ex_lock) {
            if ($this->Stage_ip_ex->getProcessId() >= $pID) {
                $result->addCommandDeleted($this->Stage_ip_ex);
                $this->Stage_ip_ex_lock = false;
            }
        }
        if ($this->Stage_ex_ro_lock) {
            if ($this->Stage_ex_ro->getBasicCommand()->getProcessId() >= $pID) {
                $result->addCommandDeleted($this->Stage_ex_ro->getBasicCommand());
                $this->Stage_ex_ro_lock = false;
            }
        }
        return $result;
    }
}