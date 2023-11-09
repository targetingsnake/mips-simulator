<?php

/**
 * controller of superskalar in order pipeline
 */
class ControllerSuperSkalarIO extends ControllerSuperscalarStub
{
    /**
     * @var ExecutePipelineContainer container with multiple part pipelines
     */
    private ExecutePipelineContainer $ExecutionPipelines;

    /**
     * @var InstructionScheduler holds instruction sheduler
     */
    private InstructionScheduler $i_scheduler;

    /**
     * @var Buffer Buffer between instruction decode and instruction scheduler
     */
    protected Buffer $Stage_id_is;

    /**
     * initializes variables and parent class
     * @param string $programmCode program code as text
     * @param int $jmpPred Jump prediction selection
     * @param boolean $res_fwd Enables or disables result forwarding
     * @param int $pipelines Defines how many pipelines are used
     */
    public function __construct(string $programmCode, int $jmpPred, bool $res_fwd, int $pipelines)
    {
        parent::__construct($programmCode, $jmpPred, $res_fwd, $pipelines);
        $this->ExecutionPipelines = new ExecutePipelineContainer($pipelines, $programmCode, $jmpPred, $res_fwd);
        $this->i_scheduler = new InstructionScheduler();
        $this->Stage_id_is = new Buffer($pipelines);
    }

    /**
     * executes simulation
     */
    public function run(): void
    {
        $loopCondition = false;
        do {
            for ($i = 0; $i < $this->pipelines; $i++) {
                $this->executePipeline($i);
            }

            //process InstructionScheduler
            if ($this->Stage_id_is->isLocked()) {
                $this->instruction_scheduler();
            }

            //process instruction decode
            if ($this->Stage_if_id->isLocked()) {
                for ($i = 0; $i < $this->pipelines; $i++) {
                    $this->instruction_decode($i);
                }
            }

            //process instruction fetch
            for ($i = 0; $i < $this->pipelines; $i++) {
                $this->instruction_fetch($i);
            }

            //logs all made changes in current takt
            $this->logMemoryAndRegister();

            //increases takt
            $this->i_runTakt = $this->states->takt;
            $this->states->takt = $this->states->takt + 1;
            $loopCondition = ($this->Stage_if_id->isLocked() || $this->Stage_id_is->isLocked());
            $loopCondition = ($loopCondition || $this->states->hold_loop);
            for ($i = 0; $i < $this->pipelines; $i++) {
                $loopCondition = $loopCondition || $this->ExecutionPipelines->getPipeline($i)->isRunnable();
            }
            if ($this->states->hold_loop && $this->states->i_programmMemory->getProgrammLength() <= $this->states->ProgrammPointer) {
                $loopCondition = false;
            }
        } while ($loopCondition);
    }

    /**
     * represents the instruction fetch of a cpu
     */
    protected function instruction_fetch(int $execPos): void
    {
        if ($this->Stage_if_id->isLocked()) {
            if (!$this->Stage_if_id->isUsed($execPos)) {
                return;
            }
            $cmd = $this->Stage_if_id->getCommand($execPos);
            $cmd = $this->i_instructionFetch->processCmd($cmd, $this->states->takt);
            $this->Stage_if_id->insertCommand($execPos, $cmd);
            return;
        }

        if ($this->states->wait_jump && $this->jumpPrediction == 0) {
            $this->Stage_if_id->setLocked(true);
            return;
        }

        if ($this->states->reset) {
            if ($execPos == $this->pipelines - 1) {
                $this->states->reset = false;
            }
            return;
        }

        if ($this->states->hold_loop) {
            $this->states->hold_loop = false;
        }

        if ($this->states->counter_add_shift > 0) {
            if ($execPos == $this->pipelines - 1) {
                $this->states->counter_add_shift = $this->states->counter_add_shift - 1;
                $this->states->hold_loop = true;
            }
            return;
        }

        if ($this->states->i_programmMemory->getProgrammLength() <= $this->states->ProgrammPointer) {
            for ($i = 0; $i < $this->pipelines; $i++) {
                if ($this->Stage_if_id->isUsed($i)) {
                    $this->Stage_if_id->setLocked(true);
                    return;
                }
            }
            return;
        }

        if (!$this->result_forward && $this->states->wait_if) {
            $this->states->wait_if = false;
            $this->states->hold_loop = true;
            return;
        }

        $cmd = clone $this->states->i_programmMemory->getCommand($this->states->ProgrammPointer);
        $cmd->setProcessId($this->processID);
        $this->processID = $this->processID + 1;

        switch ($cmd->getCommandType()) {
            case 1:
            case 5:
            case 6:
                if ($this->jumpPrediction == 0) {
                    $this->states->wait_jump = true;
                    $this->states->wait_if = true;
                }
                break;
            default:
                break;
        }
        $this->states->ProgrammPointer = $this->states->ProgrammPointer + 1;

        $cmd->setPrediction(false);

        if ($this->jumpPrediction > 0) {
            if ($cmd->getCommandType() == 5 || $cmd->getCommandType() == 6 || $cmd->getCommandType() == 1) {
                if ($this->states->i_predict->getPrediction($cmd->getId())) {
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
                    $this->states->i_programmPointer_hold[$cmd->getProcessId()] = $this->states->ProgrammPointer;
                    $this->states->ProgrammPointer = $this->states->i_programmMemory->getLabel($label);
                }
            }
        }

        $cmd = $this->i_instructionFetch->processCmd($cmd, $this->states->takt);
        $this->Stage_if_id->insertCommand($execPos, $cmd);
        $this->Stage_if_id->setUsed($execPos, true);
        if ($execPos == ($this->pipelines - 1)) {
            $this->Stage_if_id->setLocked(true);
        }
        $this->states->hold_loop = false;
    }

    /**
     * represents the instruction decode of a cpu
     */
    private function instruction_decode(int $execPos): void
    {
        if ($this->Stage_id_is->isLocked()) {
            return;
        }
        if ($this->Stage_if_id->isUsed($execPos)) {
            $cmd = $this->i_instructionDecode->processCmd($this->Stage_if_id->getCommand($execPos), $this->states->takt);
            $this->Stage_id_is->insertCommand($execPos, $cmd);
            $this->Stage_if_id->setUsed($execPos, false);
            $this->Stage_id_is->setUsed($execPos, true);
        } else {
            $this->Stage_id_is->setUsed($execPos, false);
        }
        if ($execPos == ($this->pipelines - 1)) {
            $this->Stage_if_id->setLocked(false);
            $this->Stage_id_is->setLocked(true);
        }
    }

    /**
     * represents instruction scheduler;
     */
    private function instruction_scheduler(): void
    {
        $stall = false;
        for ($i = 0; $i < $this->pipelines; $i++) {
            if ($this->Stage_id_is->isUsed($i)) {
                $cmd = $this->Stage_id_is->getCommand($i);
                $cmd = $this->i_scheduler->processCmd($cmd, $this->states->takt);
                if ($this->i_scheduler->isCommandRunnable($cmd) && !$stall) {
                    if ($this->ExecutionPipelines->getPipeline($i)->isBusy() == false) {
                        $pipeline = $this->ExecutionPipelines->getPipeline($i);
                        $this->i_scheduler->setRegistersForCommand($cmd, true);
                        $pipeline->loadNext($cmd);
                        $this->ExecutionPipelines->InsertPipeline($i, $pipeline);
                        $this->Stage_id_is->setUsed($i, false);
                    } else {
                        $stall = true;
                        $this->Stage_id_is->insertCommand($i, $cmd);
                    }
                } else {
                    $stall = true;
                    $this->Stage_id_is->insertCommand($i, $cmd);
                }
            }
        }
        if (!$stall) {
            $this->Stage_id_is->setLocked(false);
        }
    }

    /**
     * executes part pipeline
     * @param int $execPos identifier of part pipeline which will be executed
     */
    public function executePipeline(int $execPos): void
    {
        $pipeline = $this->ExecutionPipelines->getPipeline($execPos);
        $this->states = $pipeline->do_takt($this->states);
        if ($pipeline->hasLogable()) {
            $log = $pipeline->getLogable();
            $this->i_programmRun[$log->getProcessId()] = $log->getStats();
        }
        $this->ExecutionPipelines->InsertPipeline($execPos, $pipeline);
        if ($this->states->clear_Pipeline) {
            $this->reset($this->states->clear_processID);
        }
        if ($this->states->set_registerFree_lock) {
            $this->states->set_registerFree_lock = false;
            if (count($this->states->setRegisterReadFree) > 0) {
                foreach ($this->states->setRegisterReadFree as $reg) {
                    $this->i_scheduler->setRegisterStateRead($reg, false);
                }
                $this->states->setRegisterReadFree = array();
            }
            if (count($this->states->setRegisterWriteFree) > 0) {
                foreach ($this->states->setRegisterWriteFree as $reg) {
                    $this->i_scheduler->setRegisterStateWrite($reg, false);
                }
                $this->states->setRegisterWriteFree = array();
            }

        }
    }

    /**
     * resets whole pipeline
     * @param int $processID process identifier of last executed jump command
     */
    private function reset(int $processID): void
    {
        for ($i = 0; $i < $this->pipelines; $i++) {
            $pipeline = $this->ExecutionPipelines->getPipeline($i);
            $result = $pipeline->reset($processID, $this->states->takt);
            if (count($result) > 0){
                $keys = array_keys($result);
                foreach ($keys as $key) {
                    $this->i_programmRun[$key] = $result[$key];
                }
            }
            $this->ExecutionPipelines->InsertPipeline($i, $pipeline);
        }
        for ($i = 0; $i < $this->pipelines; $i++) {
            if ($this->Stage_id_is->isUsed($i)) {
                $cmd = $this->Stage_id_is->getCommand($i);
                $cmd->log_history("IS", $this->states->takt);
                $cmd->log_history("FL", $this->states->takt + 1);
                $this->i_programmRun[$cmd->getProcessId()] = $cmd->getStats();
                $this->Stage_id_is->setUsed($i, false);
            }
        }
        $this->Stage_id_is->setLocked(false);
        for ($i = 0; $i < $this->pipelines; $i++) {
            if ($this->Stage_if_id->isUsed($i)) {
                $cmd = $this->Stage_if_id->getCommand($i);
                $cmd->log_history("ID", $this->states->takt);
                $cmd->log_history("FL", $this->states->takt + 1);
                $this->i_programmRun[$cmd->getProcessId()] = $cmd->getStats();
                $this->Stage_if_id->setUsed($i, false);
            }
        }
        $this->Stage_if_id->setLocked(false);
        $this->i_scheduler->reset();
        $this->states->reset();
        $this->states->hold_loop = true;
        $this->abortWithFL = true;
    }
}