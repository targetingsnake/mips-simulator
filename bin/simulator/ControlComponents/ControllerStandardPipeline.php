<?php

/**
 * controller of standard pipeline
 */
class ControllerStandardPipeline extends ControllerMain
{
    /**
     * @var Command Buffer between instruction fetch and instruction decode
     */
    protected Command $Stage_if_id;

    /**
     * @var Command Buffer between instruction decode and operand fetch
     */
    protected Command $Stage_id_of;

    /**
     * @var Command Buffer between operand fetch and execution
     */
    protected Command $Stage_of_ex;

    /**
     * @var Command Buffer between execution and write back
     */
    protected Command $Stage_ex_wb;

    /**
     * @var bool Lock for buffer between instruction fetch and instruction decode
     */
    protected bool $Stage_if_id_lock;

    /**
     * @var bool Lock for buffer between instruction decode and operand fetch
     */
    protected bool $Stage_id_of_lock;

    /**
     * @var bool Lock for buffer between operand fetch and execution
     */
    protected bool $Stage_of_ex_lock;

    /**
     * @var bool Lock for buffer between execution and write back
     */
    protected bool $Stage_ex_wb_lock;

    /**
     * @var bool defines if instruction fetch has to wait for a jump command to be executed
     */
    protected bool $wait_jump;

    /**
     * @var bool defines if result forward is active
     */
    protected bool $result_forward;

    /**
     * @var bool states if loop has to do another round
     */
    protected bool $hold_loop;

    /**
     * @var int defines which jump prediction is used
     */
    protected int $jumpPrediction;

    /**
     * @var int states how many cycles instruction fetch has to wait
     */
    protected int $counter_add_shift;

    /**
     * @var array saves of program pointer
     */
    protected array $i_programmPointer_hold;

    /**
     * @var JumpPrediction1|JumpPrediction2 used jump prediction
     */
    protected JumpPrediction1 $i_predict;

    /**
     * @var array holds registers for a delayed write back
     */
    protected array $delayedWriteBack;

    /**
     * @var Registers holds a shadow register set
     */
    protected Registers $i_shadow_registers;


    /**
     * Main run class for unpipelined run
     * @param string $programmCode programmcode as text
     * @param int $jmpPred Jump predicition selection
     * @param boolean $res_fwd Enables or disables result forwarding
     */
    public function __construct(string $programmCode, int $jmpPred, bool $res_fwd)
    {
        parent::__construct($programmCode);
        $this->Stage_if_id = new Command("", 0);
        $this->Stage_id_of = new Command("", 0);
        $this->Stage_of_ex = new Command("", 0);
        $this->Stage_ex_wb = new Command("", 0);
        $this->Stage_if_id_lock = false;
        $this->Stage_id_of_lock = false;
        $this->Stage_of_ex_lock = false;
        $this->Stage_ex_wb_lock = false;
        $this->wait_jump = false;
        $this->jumpPrediction = $jmpPred;
        $this->result_forward = $res_fwd;
        $this->counter_add_shift = 0;
        $this->hold_loop = false;
        $this->i_programmPointer_hold = array();
        $this->delayedWriteBack = array();
        $this->i_shadow_registers = new Registers();
        switch ($jmpPred) {
            case 0:
                $this->i_predict = new JumpPrediction0();
            case 1:
                $this->i_predict = new JumpPrediction1();
                break;
            case 2:
                $this->i_predict = new JumpPrediction2();
                break;
        }
    }

    /**
     * executes simulation
     */
    public function run(): void
    {
        do {
            $this->abortWithFL = false;
            // execute writeback
            if ($this->Stage_ex_wb_lock) {
                $this->writeBack();
            }

            // excute command
            if ($this->Stage_of_ex_lock) {
                $this->execute_command();
            }

            // process operand fetch
            if ($this->Stage_id_of_lock) {
                $this->operand_fetch();
            }

            //proceass instruction decode
            if ($this->Stage_if_id_lock) {
                $this->instruction_decode();
            }

            //process instruction fetch
            $this->instruction_fetch();

            if (!$this->result_forward) {
                foreach ($this->delayedWriteBack as $reg) {
                    $this->i_registers->setRegisterContent($reg);
                }
            }

            //logs all made changes in current takt
            $this->logMemoryAndRegister();
            $this->logBHT();

            //increases takt
            $this->i_runTakt = $this->takt;
            $this->takt = $this->takt + 1;
        } while (($this->Stage_if_id_lock || $this->Stage_id_of_lock || $this->Stage_of_ex_lock || $this->Stage_ex_wb_lock) || $this->hold_loop);
        if (!$this->abortWithFL) {
            $this->i_programmRun[$this->processID] = array(
                "history" => array($this->takt - 4 => "IF", $this->takt - 3 => "ID", $this->takt - 2 => "OF", $this->takt - 1 => "EX"),
                "command" => ""
            );
            $this->processID = $this->processID + 1;
            $this->i_programmRun[$this->processID] = array(
                "history" => array($this->takt - 3 => "IF", $this->takt - 2 => "ID", $this->takt - 1 => "OF"),
                "command" => ""
            );
            $this->processID = $this->processID + 1;
            $this->i_programmRun[$this->processID] = array(
                "history" => array($this->takt - 2 => "IF", $this->takt - 1 => "ID"),
                "command" => ""
            );
            $this->processID = $this->processID + 1;
            $this->i_programmRun[$this->processID] = array(
                "history" => array($this->takt - 1 => "IF"),
                "command" => ""
            );
        }
    }

    /**
     * represents the instruction fetch of a cpu
     */
    protected function instruction_fetch(): void
    {
        if ($this->Stage_if_id_lock) {
            $this->Stage_if_id = $this->i_instructionFetch->processCmd($this->Stage_if_id, $this->takt);
            return;
        }

        if ($this->wait_jump && $this->jumpPrediction == 0) {
            return;
        }

        if ($this->counter_add_shift > 0) {
            $this->counter_add_shift = $this->counter_add_shift - 1;
            return;
        }

        if ($this->i_programmMemory->getProgrammLength() <= $this->ProgrammPointer) {
            return;
        }

        if ((($this->Stage_id_of_lock || $this->Stage_of_ex_lock || $this->Stage_ex_wb_lock) == false) && !$this->hold_loop && $this->takt > 0) {
            $this->hold_loop = true;
            return;
        }

        $cmd = clone $this->i_programmMemory->getCommand($this->ProgrammPointer);
        $cmd->setProcessId($this->processID);
        switch ($cmd->getCommandType()) {
            case 1:
            case 5:
            case 6:
                if ($this->jumpPrediction == 0) {
                    $this->wait_jump = true;
                }
                break;
            default:
                break;
        }
        $this->ProgrammPointer = $this->ProgrammPointer + 1;

        $cmd->setPrediction(false);

        if ($this->jumpPrediction > 0) {
            if ($cmd->getCommandType() == 5 || $cmd->getCommandType() == 6 || $cmd->getCommandType() == 1) {
                if ($this->i_predict->getPrediction($cmd->getId())) {
                    $cmd->setPrediction(true);
                    $label = -1;
                    switch ($cmd->getCommandType()) {
                        case 1:
                            $label = $cmd->getArguments()[1];
                            break;
                        case 5:
                            $label = $cmd->getArguments()[2];
                            break;
                        case 6:
                            $label = $cmd->getArguments()[3];
                            break;
                    }
                    $this->i_programmPointer_hold[$cmd->getProcessId()] = $this->ProgrammPointer;
                    $this->ProgrammPointer = $this->i_programmMemory->getLabel($label);
                }
            }
        }
        $this->Stage_if_id = $this->i_instructionFetch->processCmd($cmd, $this->takt);
        $this->Stage_if_id_lock = true;
        $this->processID = $this->processID + 1;
        $this->hold_loop = false;

    }

    /**
     * represents the instruction decode of a cpu
     */
    protected function instruction_decode(): void
    {
        if ($this->Stage_id_of_lock) {
            $this->Stage_id_of = $this->i_instructionDecode->processCmd($this->Stage_id_of, $this->takt);
            return;
        }
        $this->Stage_id_of = $this->i_instructionDecode->processCmd($this->Stage_if_id, $this->takt);
        $this->Stage_id_of_lock = true;
        $this->Stage_if_id_lock = false;
    }

    /**
     * represents operand fetch of cpu
     */
    protected function operand_fetch(): void
    {
        if ($this->Stage_of_ex_lock) {
            $this->Stage_of_ex = $this->i_operandFetch->processCmd($this->Stage_of_ex, $this->takt);
            return;
        }

        $cmd = $this->Stage_id_of;
        $lock = false;
        $this->Stage_of_ex = $this->i_operandFetch->processCmd($cmd, $this->takt);
        switch ($cmd->getCommandType()) {
            case 0:
                $lock = $this->i_registers->getLockedState($cmd->getArguments()[2]);
                break;
            case 2:
                $lock = $this->i_registers->getLockedState($cmd->getArguments()[2]) || $this->i_registers->getLockedState($cmd->getArguments()[3]);
                break;
            case 3:
            case 5:
                if ($cmd->getCommand() != "lw") {
                    $lock = $this->i_registers->getLockedState($cmd->getArguments()[1]);
                }
                break;
            case 6:
                $lock = $this->i_registers->getLockedState($cmd->getArguments()[1]) || $this->i_registers->getLockedState($cmd->getArguments()[2]);
                break;
        }

        if (!$lock) {
            switch ($cmd->getCommandType()) {
                case 0:
                case 2:
                    $register = $this->i_registers->getRegisterContent($cmd->getArguments()[1]);
                    $register->setLockState(true);
                    $this->i_registers->setRegisterContent($register);
                    break;
                case 3:
                    if ($this->Stage_id_of->getCommand() == "lw") {
                        $register = $this->i_registers->getRegisterContent($cmd->getArguments()[1]);
                        $register->setLockState(true);
                        $this->i_registers->setRegisterContent($register);
                    }
                    break;
            }
            $this->Stage_of_ex_lock = true;
            $this->Stage_id_of_lock = false;
        }
    }

    /**
     * represents execution step of cpu pipeline
     */
    protected function execute_command(): void
    {
        if ($this->Stage_ex_wb_lock) {
            $this->Stage_ex_wb = $this->i_execution->processCmd($this->Stage_ex_wb, $this->takt);
            return;
        }
        if ($this->Stage_of_ex->getExecutionTimeRemaining() == 1) {
            $result = $this->execute($this->Stage_of_ex, $this->ProgrammPointer);
            if (($result['cmd']->getCommandType() == 5 || $result['cmd']->getCommandType() == 6 || $result['cmd']->getCommandType() == 1)) {
                $this->wait_jump = false;
                if ($this->jumpPrediction == 0) {
                    $this->counter_add_shift = 1;
                }
            }
            $this->Stage_ex_wb_lock = true;
            if ($this->jumpPrediction > 0) {
                if (($result['cmd']->getCommandType() == 5 || $result['cmd']->getCommandType() == 6 || $result['cmd']->getCommandType() == 1)) {
                    $this->i_predict->addPrediction($result['cmd']->getId(), $result['jmp']);
                    if ($result['cmd']->getPrediction() != $result['jmp']) {
                        $currentCmd = $this->i_execution->processCmd($result['cmd'], $this->takt);
                        $currentCmd->log_history("FL", $this->takt + 1);
                        $this->i_programmRun[$currentCmd->getProcessId()] = $currentCmd->getStats();
                        $this->Stage_ex_wb_lock = false;
                        if ($this->Stage_if_id_lock) {
                            $this->Stage_if_id->log_history("ID", $this->takt);
                            $this->Stage_if_id->log_history("FL", $this->takt + 1);
                            $this->i_programmRun[$this->Stage_if_id->getProcessId()] = $this->Stage_if_id->getStats();
                        } else {
                            $this->i_programmRun[$this->processID] = array(
                                "history" => array($this->takt - 2 => "IF", $this->takt - 1 => "ID", $this->takt => "OF", $this->takt + 1 => "FL"),
                                "command" => ""
                            );
                            $this->processID = $this->processID + 1;
                        }
                        if ($this->Stage_id_of_lock) {
                            $this->Stage_id_of->log_history("OF", $this->takt);
                            $this->Stage_id_of->log_history("FL", $this->takt + 1);
                            $this->i_programmRun[$this->Stage_id_of->getProcessId()] = $this->Stage_id_of->getStats();
                        } else {
                            $this->i_programmRun[$this->processID] = array(
                                "history" => array($this->takt - 1 => "IF", $this->takt => "ID", $this->takt + 1 => "FL"),
                                "command" => ""
                            );
                            $this->processID = $this->processID + 1;
                        }
                        $this->i_programmRun[$this->processID] = array(
                            "history" => array($this->takt => "IF", $this->takt + 1 => "FL"),
                            "command" => ""
                        );
                        $this->counter_add_shift = 0;
                        $this->processID = $this->processID + 1;
                        $this->Stage_if_id_lock = false;
                        $this->Stage_id_of_lock = false;
                        if (!$result['jmp']) {
                            $this->ProgrammPointer = $this->i_programmPointer_hold[$result['cmd']->getProcessId()];
                            unset($this->i_programmPointer_hold[$result['cmd']->getProcessId()]);
                        } else {
                            $this->ProgrammPointer = $result['pgm'];
                        }
                        $this->abortWithFL = true;
                    }
                }
            }
            $this->Stage_of_ex_lock = false;
            $this->Stage_ex_wb = $this->i_execution->processCmd($result['cmd'], $this->takt);;
            if ($this->jumpPrediction == 0) {
                $this->ProgrammPointer = $result['pgm'];
            }
            if ($this->result_forward) {
                $reg_name = $this->i_writeBack->getRegisterOfCommand($this->Stage_ex_wb->getProcessId())->getName();
                $reg = $this->i_registers->getRegisterContent($reg_name);
                $reg->setLockState(false);
                $this->i_registers->setRegisterContent($reg);
            }
        } else {
            $this->Stage_of_ex->decreaseExecutionTime();
            $this->Stage_of_ex = $this->i_execution->processCmd($this->Stage_of_ex, $this->takt);
        }
    }

    /**
     * represents writeback stage of cpu
     */
    protected function writeBack(): void
    {
        if (!$this->Stage_ex_wb_lock) {
            return;
        }
        $log = $this->i_writeBack->processCmd($this->Stage_ex_wb, $this->takt);
        $register = $this->i_writeBack->WriteBack($log->getProcessId());
        if ($register != null) {
            $register->setLockState(false);
            if ($this->result_forward) {
                $this->i_registers->setRegisterContent($register);
            } else {
                $this->delayedWriteBack[] = $register;
            }
        }
        $this->Stage_ex_wb_lock = false;
        $this->i_programmRun[$log->getProcessId()] = $log->getStats();
    }

    /**
     * logs branch history table content
     */
    private function logBHT(): void
    {
        $this->i_BranchHistory[$this->takt] = $this->i_predict->getStats();
    }
}