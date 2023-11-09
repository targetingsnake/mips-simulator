<?php

class PipelineSuperskalarExec extends ControllerMain
{
    /**
     * @var Command Buffer between Instruction Scheduler and Operand Fetch
     */
    private Command $Stage_is_of;

    /**
     * @var Command Buffer between Operand Fetch and Execution
     */
    private Command $Stage_of_ex;

    /**
     * @var Command Buffer between Execution and Write Back
     */
    private Command $Stage_ex_wb;

    /**
     * @var Command Buffer between Writeback and Log
     */
    private Command $Stage_wb_lg;

    /**
     * @var bool Lock for Buffer between Instruction Scheduler and Operand Fetch
     */
    private bool $Stage_is_of_lock;

    /**
     * @var bool Lock for Buffer between Operand Fetch and Execution
     */
    private bool $Stage_of_ex_lock;

    /**
     * @var bool Lock for Buffer between Execution and Write Back
     */
    private bool $Stage_ex_wb_lock;

    /**
     * @var bool Lock for Buffer between Writeback and Log
     */
    private bool $Stage_wb_lg_lock;

    /**
     * @var bool state of Operand fetch preload
     */
    private bool $of_preload;

    /**
     * @var int defines used jump prediction
     */
    private int $jump_prediction;

    /**
     * @var bool defines if result forward is used
     */
    private bool $result_forward;

    /**
     * @var StateObject contains state of processor
     */
    public StateObject $state;

    /**
     * initializes all variables and calls constructor of parent class
     * @param string $programmCode simulated program code
     * @param int $jmpPred defines which jump prediction is used
     * @param bool $res_fwd defines if result forward is used
     */
    public function __construct(string $programmCode, int $jmpPred, bool $res_fwd)
    {
        parent::__construct($programmCode);
        $this->of_preload = false;
        $this->jump_prediction = $jmpPred;
        $this->result_forward = $res_fwd;
        $this->Stage_wb_lg_lock = false;
        $this->Stage_ex_wb_lock = false;
        $this->Stage_of_ex_lock = false;
        $this->Stage_is_of_lock = false;
        $this->Stage_wb_lg = new Command("", 0);
        $this->Stage_ex_wb = new Command("", 0);
        $this->Stage_of_ex = new Command("", 0);
        $this->Stage_is_of = new Command("", 0);
    }

    /**
     * executes one cycle
     * @param StateObject $state current state of processor
     * @return StateObject next state of processor after this cycle
     */
    public function do_takt(StateObject $state)
    {
        $this->state = $state;

        /**
         * executes writeback
         */

        if ($this->Stage_ex_wb_lock) {
            $this->writeBack();
        }

        /**
         * executes command
         */
        if ($this->Stage_of_ex_lock) {
            $this->execute_command();
        }

        /**
         * execute operand fetch
         */
        if ($this->Stage_is_of_lock) {
            $this->operand_fetch();
        }

        return $this->state;
    }

    /**
     * loads next command in part pipeline
     * @param Command $cmd next Command
     */
    public function loadNext(Command $cmd): void
    {
        $this->Stage_is_of = $cmd;
        $this->Stage_is_of_lock = true;
    }

    /**
     * checks if pipeline is already busy
     * @return bool true if busy
     */
    public function isBusy(): bool
    {
        return $this->Stage_is_of_lock;
    }

    /**
     * checks if pipeline has a logable command
     * @return bool true if it has a command to log
     */
    public function hasLogable(): bool
    {
        return $this->Stage_wb_lg_lock;
    }

    /**
     * get command to log
     * @return Command command to log
     */
    public function getLogable(): Command
    {
        $this->Stage_wb_lg_lock = false;
        return $this->Stage_wb_lg;
    }

    /**
     * represents operand fetch of cpu
     */
    protected function operand_fetch(): void
    {
        if ($this->Stage_is_of_lock && $this->Stage_of_ex_lock) {
            $this->Stage_is_of->log_history("IS", $this->state->takt);
        }

        if ($this->Stage_of_ex_lock) {
            $this->Stage_of_ex = $this->i_operandFetch->processCmd($this->Stage_of_ex, $this->state->takt);
            return;
        }

        if (!$this->result_forward && $this->state->wait_of) {
            $this->state->wait_of = false;
            return;
        }

        $cmd = $this->Stage_is_of;
        if ($this->of_preload) {
            $cmd = $this->Stage_of_ex;
        }
        $this->Stage_of_ex = $this->i_operandFetch->processCmd($cmd, $this->state->takt);
        switch ($cmd->getCommandType()) {
            case 0:
            case 2:
                if ($this->result_forward) {
                    $this->state->setRegisterWriteFree[] = $cmd->getArguments()[1];
                    $this->state->set_registerFree_lock = true;
                }
                break;
            case 3:
                if ($this->Stage_is_of->getCommand() == "lw") {
                    if ($this->result_forward) {
                        $this->state->setRegisterWriteFree[] = $cmd->getArguments()[1];
                        $this->state->set_registerFree_lock = true;
                    }
                }
                break;
        }

        $this->Stage_of_ex_lock = true;
        $this->Stage_is_of_lock = false;
    }

    /**
     * represents execution step of cpu pipeline
     */
    protected function execute_command(): void
    {
        if ($this->state->wait_of) {
            return;
        }
        if ($this->Stage_ex_wb_lock) {
            $this->Stage_ex_wb = $this->i_execution->processCmd($this->Stage_ex_wb, $this->state->takt);
            return;
        }
        if ($this->Stage_of_ex->getExecutionTimeRemaining() == 1) {
            $this->i_memory = $this->state->i_memory;
            $result = $this->execute($this->Stage_of_ex, $this->state->ProgrammPointer, $this->state->i_shadow_registers);
            if ($result['jmp']) {
                if ($this->jump_prediction == 0) {
                    if (!$this->result_forward) {
                        $this->state->counter_add_shift = $this->state->counter_add_shift + 1;
                    }
                    $this->state->counter_add_shift = $this->state->counter_add_shift + 1;
                }
            }
            if (($result['cmd']->getCommandType() == 5 || $result['cmd']->getCommandType() == 6 || $result['cmd']->getCommandType() == 1)) {
                $this->state->wait_jump = false;
            }
            if ($this->jump_prediction > 0) {
                if (($result['cmd']->getCommandType() == 5 || $result['cmd']->getCommandType() == 6 || $result['cmd']->getCommandType() == 1)) {
                    $this->state->i_predict->addPrediction($result['cmd']->getId(), $result['jmp']);
                    if ($result['cmd']->getPrediction() != $result['jmp']) {
                        $this->state->clear_Pipeline = true;
                        $this->state->clear_processID = $result['cmd']->getProcessId();
                        if (!$result['jmp']) {
                            $this->state->ProgrammPointer = $this->state->i_programmPointer_hold[$result['cmd']->getProcessId()];
                            unset($this->state->i_programmPointer_hold[$result['cmd']->getProcessId()]);
                            if (!$this->result_forward) {
                                $this->state->counter_add_shift = $this->state->counter_add_shift + 1;
                            }
                            $this->state->counter_add_shift = $this->state->counter_add_shift + 1;
                        } else {
                            $this->state->ProgrammPointer = $result['pgm'];
                        }
                    }
                }
            }
            $this->Stage_of_ex_lock = false;
            $this->Stage_ex_wb_lock = true;
            $this->Stage_ex_wb = $this->i_execution->processCmd($result['cmd'], $this->state->takt);;
            if ($this->jump_prediction == 0) {
                $this->state->ProgrammPointer = $result['pgm'];
            }
            switch ($this->Stage_ex_wb->getCommandType()) {
                case 0:
                    $this->state->setRegisterReadFree[] = $this->Stage_ex_wb->getArguments()[2];
                    $this->state->set_registerFree_lock = true;
                    break;
                case 1:
                    break;
                case 2:
                    $this->state->setRegisterReadFree[] = $this->Stage_ex_wb->getArguments()[2];
                    $this->state->setRegisterReadFree[] = $this->Stage_ex_wb->getArguments()[3];
                    $this->state->set_registerFree_lock = true;
                    break;
                case 3:
                    if ($this->Stage_ex_wb->getCommand() == "sw") {
                        $this->state->setRegisterReadFree[] = $this->Stage_ex_wb->getArguments()[1];
                        $this->state->set_registerFree_lock = true;
                    }
                    break;
                case 5:
                    $this->state->setRegisterReadFree[] = $this->Stage_ex_wb->getArguments()[1];
                    $this->state->set_registerFree_lock = true;
                    break;
                case 6:
                    $this->state->setRegisterReadFree[] = $this->Stage_ex_wb->getArguments()[1];
                    $this->state->setRegisterReadFree[] = $this->Stage_ex_wb->getArguments()[2];
                    $this->state->set_registerFree_lock = true;
                    break;
            }
            $register = $this->i_writeBack->getRegisterOfCommand($this->Stage_ex_wb->getProcessId());
            if (!is_null($register)) {
                $register->setLockState(false);
                $this->state->i_shadow_registers->setRegisterContent($register);
            }
            $this->state->i_memory = $this->i_memory;
        } else {
            $this->Stage_of_ex->decreaseExecutionTime();
            $this->Stage_of_ex = $this->i_execution->processCmd($this->Stage_of_ex, $this->state->takt);
        }
    }

    /**
     * represents write back stage of cpu
     */
    protected function writeBack(): void
    {
        if (!$this->Stage_ex_wb_lock) {
            return;
        }
        $log = $this->i_writeBack->processCmd($this->Stage_ex_wb, $this->state->takt);
        $register = $this->i_writeBack->WriteBack($log->getProcessId());
        if ($register != null) {
            $register->setLockState(false);
            if (!$this->result_forward) {
                $this->state->setRegisterWriteFree[] = $register->getName();
                $this->state->set_registerFree_lock = true;
            }
            $this->state->i_registers->setRegisterContent($register);
        }
        $this->Stage_wb_lg = $log;
        $this->Stage_ex_wb_lock = false;
        $this->Stage_wb_lg_lock = true;
    }

    /**
     * resets part of pipeline
     * @param int $processId process Identifier of last calculated jump command
     * @param int $takt current tact
     * @return array returns gathered information for log
     */
    public function reset(int $processId, int $takt): array
    {
        $result = array();
        if (($this->Stage_wb_lg->getProcessId() >= $processId) && $this->Stage_wb_lg_lock) {
            $this->Stage_wb_lg_lock = false;
            $result[$this->Stage_wb_lg->getProcessId()] = $this->Stage_wb_lg->getStats();
        }
        if (($this->Stage_ex_wb->getProcessId() >= $processId) && $this->Stage_ex_wb_lock) {
            $this->Stage_ex_wb_lock = false;
            $this->Stage_ex_wb->log_history("FL", $takt + 1);
            $result[$this->Stage_ex_wb->getProcessId()] = $this->Stage_ex_wb->getStats();
        }
        if (($this->Stage_of_ex->getProcessId() >= $processId) && $this->Stage_of_ex_lock) {
            $this->Stage_of_ex_lock = false;
            $this->Stage_of_ex->log_history("FL", $takt + 1);
            $this->Stage_of_ex->log_history("EX", $takt);
            $result[$this->Stage_of_ex->getProcessId()] = $this->Stage_of_ex->getStats();
        }
        if (($this->Stage_is_of->getProcessId() >= $processId) && $this->Stage_is_of_lock) {
            $this->Stage_is_of_lock = false;
            $this->Stage_is_of->log_history("FL", $takt + 1);
            $this->Stage_is_of->log_history("OF", $takt);
            $result[$this->Stage_is_of->getProcessId()] = $this->Stage_is_of->getStats();
        }
        return $result;
    }

    /**
     * checks if pipeline has a command to run
     * @return bool true if part pipeline is runable
     */
    public function isRunnable(): bool
    {
        return $this->Stage_is_of_lock || $this->Stage_of_ex_lock || $this->Stage_ex_wb_lock || $this->Stage_wb_lg_lock;
    }
}