<?php

/**
 * controller of superskalar out of order pipeline
 */
class ControllerSuperSkalarOoO extends ControllerSuperscalarStub
{
    /**
     * @var InstructionPool holds instruction pool
     */
    private InstructionPool $i_instructionPool;

    /**
     * @var ReorderBuffer hold reorder buffer
     */
    private ReorderBuffer $i_ReorderBuffer;

    /**
     * @var Buffer Buffer between instruction decode and instruction pool
     */
    private Buffer $Stage_id_ip;

    /**
     * @var BufferReOrderWriteBack Buffer between reorder and write back
     */
    private BufferReOrderWriteBack $Stage_ro_wb;

    /**
     * @var ExecutePipelineContainerOoO container with all part pipelines
     */
    private ExecutePipelineContainerOoO $ExecutionPipelines;

    /**
     * @var array array with registers which should be set as free
     */
    private array $delayedFree;

    /**
     * Main run class for unpipelined run
     * @param string $programmCode programmcode as text
     * @param int $jmpPred Jump predicition selection
     * @param int $pipelines Defines how many pipelines are used
     */
    public function __construct(string $programmCode, int $jmpPred, int $pipelines)
    {
        parent::__construct($programmCode, $jmpPred, true, $pipelines);
        $this->i_instructionPool = new InstructionPool();
        $this->i_ReorderBuffer = new ReorderBuffer();
        $this->Stage_id_ip = new Buffer($pipelines);
        $this->Stage_ro_wb = new BufferReOrderWriteBack($pipelines);
        $this->ExecutionPipelines = new ExecutePipelineContainerOoO($pipelines, $programmCode, $jmpPred);
        $this->delayedFree = array();
        switch ($jmpPred) {
            case 0:
                $this->states = new StateObjectOoO(new JumpPrediction0(), $this->i_programmMemory);
                break;
            case 1:
                $this->states = new StateObjectOoO(new JumpPrediction1(), $this->i_programmMemory);
                break;
            case 2:
                $this->states = new StateObjectOoO(new JumpPrediction2(), $this->i_programmMemory);
                break;
        }
    }

    /**
     * executes simulation
     */
    public function run(): void
    {
        $loopCondition = false;
        do {

            //writeBack
            for ($i = 0; $i < $this->pipelines; $i++) {
                $this->write_back($i);
            }

            //ReorderBuffer
            if ($this->i_ReorderBuffer->hasCommands()) {
                $this->i_ReorderBuffer->processCmds($this->states->takt);
                for ($i = 0; $i < $this->pipelines; $i++) {
                    $this->reorder_buffer($i);
                }
            }

            $this->delayedFreeExecute();

            //Execution
            for ($i = 0; $i < $this->pipelines; $i++) {
                if ($this->i_instructionPool->hasCommands()) {
                    $this->execute_unit($i);
                }
            }

            if ($this->states->clear_Pipeline) {
                $this->reset($this->states->clear_processID);
            }

            //instruction pool managable
            if ($this->Stage_id_ip->isLocked()) {
                for ($i = 0; $i < $this->pipelines; $i++) {
                    $this->instruction_pool($i);
                }
            }
            if ($this->i_instructionPool->hasCommands()) {
                $this->i_instructionPool->processCmds($this->states->takt);
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
            $loopCondition = ($this->Stage_if_id->isLocked() || $this->Stage_id_ip->isLocked());
            $loopCondition = $loopCondition || $this->Stage_ro_wb->isLocked();
            $loopCondition = $loopCondition || $this->i_instructionPool->hasCommands();
            $loopCondition = $loopCondition || $this->i_ReorderBuffer->hasCommands();
            $loopCondition = ($loopCondition || $this->states->hold_loop);
            for ($i = 0; $i < $this->pipelines; $i++) {
                $loopCondition = $loopCondition || $this->ExecutionPipelines->getPipeline($i)->isRunnable();
            }
        } while ($loopCondition);
    }

    /**
     * Frees registers delayed
     */
    protected function delayedFreeExecute(): void
    {
        for ($i = 0; $i < count($this->delayedFree); $i++) {
            $this->i_instructionPool->FreeRegisters($this->delayedFree[$i]);
        }
        $this->delayedFree = array();
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

        if ($this->states->i_programmMemory->getProgrammLength() <= $this->states->ProgrammPointer) {
            for ($i = 0; $i < $this->pipelines; $i++) {
                if ($this->Stage_if_id->isUsed($i)) {
                    $this->Stage_if_id->setLocked(true);
                    return;
                }
            }
            return;
        }

        if ($this->states->counter_add_shift > 0) {
            if ($execPos == $this->pipelines - 1) {
                $this->states->counter_add_shift = $this->states->counter_add_shift - 1;
                $this->states->hold_loop = true;
            }
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
        if ($this->Stage_id_ip->isLocked()) {
            return;
        }
        if ($this->Stage_if_id->isUsed($execPos)) {
            $cmd = $this->i_instructionDecode->processCmd($this->Stage_if_id->getCommand($execPos), $this->states->takt);
            $this->Stage_id_ip->insertCommand($execPos, $cmd);
            $this->Stage_if_id->setUsed($execPos, false);
            $this->Stage_id_ip->setUsed($execPos, true);
        } else {
            $this->Stage_id_ip->setUsed($execPos, false);
        }
        if ($execPos == ($this->pipelines - 1)) {
            $this->Stage_if_id->setLocked(false);
            $this->Stage_id_ip->setLocked(true);
        }
    }

    /**
     * represents the instruction pool of a cpu
     */
    private function instruction_pool(int $execPos): void
    {
        if ($this->Stage_id_ip->isUsed($execPos)) {
            $cmd = $this->Stage_id_ip->getCommand($execPos);
            $this->i_instructionPool->addCommandToPool($cmd);
            if ($this->jumpPrediction > 0) {
                if ($cmd->getCommandType() == 5 || $cmd->getCommandType() == 6 || $cmd->getCommandType() == 1) {
                    $this->states->i_shadow_register->makeSave($cmd->getProcessId());
                }
            }
            $this->Stage_id_ip->setUsed($execPos, false);
        }
        if ($execPos == $this->pipelines - 1) {
            $this->Stage_id_ip->setLocked(false);
        }
    }

    /**
     * executes part pipeline
     * @param int $execPos identifier of a certain part pipeline
     */
    private function execute_unit(int $execPos): void
    {
        $executionUnit = $this->ExecutionPipelines->getPipeline($execPos);
        if (!$executionUnit->isBusy()) {
            if ($this->i_instructionPool->hasExecutable()) {
                $cmd = $this->i_instructionPool->getExecutable();
                $executionUnit->loadNextCommand($cmd);
            }
        }
        $this->states = $executionUnit->do_takt($this->states);
        if ($executionUnit->isReadable()) {
            $ReorderCommand = $executionUnit->getResult();
            $this->delayedFree[] = $ReorderCommand->getBasicCommand();
            if ($ReorderCommand->isRegisterSet() && !$this->states->clear_Pipeline) {
                $this->states->i_shadow_register->setRegisters($ReorderCommand->getBasicCommand()->getProcessId(), $ReorderCommand->getRegister());
            }
            $this->i_ReorderBuffer->addCommand($ReorderCommand);
        }
        $this->ExecutionPipelines->InsertPipeline($execPos, $executionUnit);
    }

    /**
     * executes simulation of reorder buffer
     * @param int $execPos identifier of buffer space
     */
    private function reorder_buffer(int $execPos): void
    {
        if ($this->Stage_ro_wb->isLocked()) {
            return;
        }
        if ($this->i_ReorderBuffer->checkNextReady()) {
            $cmd = $this->i_ReorderBuffer->getNextCommand();
            $this->Stage_ro_wb->insertCommand($execPos, $cmd);
            $this->Stage_ro_wb->setUsed($execPos, true);
        }
        if ($execPos == $this->pipelines - 1) {
            $lock = false;
            for ($j = 0; $j < $this->pipelines; $j++) {
                if ($this->Stage_ro_wb->isUsed($j)) {
                    $lock = true;
                }
            }
            $this->Stage_ro_wb->setLocked($lock);
        }
    }

    /**
     * executes write back stage
     * @param int $execPos identifier of buffer space
     */
    private function write_back(int $execPos): void
    {
        if (!$this->Stage_ro_wb->isLocked()) {
            return;
        }
        if ($this->Stage_ro_wb->isUsed($execPos)) {
            $cmd = $this->Stage_ro_wb->getCommand($execPos);
            $this->Stage_ro_wb->setUsed($execPos, false);
            $log = $this->i_writeBack->processCmd($cmd->getBasicCommand(), $this->states->takt);
            $this->i_programmRun[$log->getProcessId()] = $log->getStats();
            if ($cmd->isRegisterSet()) {
                $register = $cmd->getRegister();
                $register->setLockState(false);
                $this->states->i_registers->setRegisterContent($register);
            }
            $this->states->i_shadow_register->deleteSave($log->getProcessId());
        }
        if ($execPos == $this->pipelines - 1) {
            $this->Stage_ro_wb->setLocked(false);
        }
    }

    /**
     * reset of whole pipeline
     * @param int $pID process identifier of last executed jump command
     */
    private function reset(int $pID)
    {
        $stage_ro_wb_reset = true;
        for ($i = 0; $i < $this->pipelines; $i++) {
            if ($this->Stage_id_ip->isUsed($i)) {
                $cmd = $this->Stage_id_ip->getCommand($i);
                $cmd->log_history("IP", $this->states->takt);
                $cmd->log_history("FL", $this->states->takt + 1);
                $this->i_programmRun[$cmd->getProcessId()] = $cmd->getStats();
                $this->Stage_if_id->setUsed($i, false);
            }
            if ($this->Stage_if_id->isUsed($i)) {
                $cmd = $this->Stage_if_id->getCommand($i);
                $cmd->log_history("ID", $this->states->takt);
                $cmd->log_history("FL", $this->states->takt + 1);
                $this->i_programmRun[$cmd->getProcessId()] = $cmd->getStats();
                $this->Stage_if_id->setUsed($i, false);
            }
            if ($this->Stage_ro_wb->isUsed($i)) {
                if ($this->Stage_ro_wb->getCommand($i)->getBasicCommand()->getProcessId() >= $pID) {
                    $this->i_programmRun[$this->Stage_ro_wb->getCommand($i)->getBasicCommand()->getProcessId()] = $this->Stage_ro_wb->getCommand($i)->getBasicCommand()->getStats();
                    $this->Stage_ro_wb->setUsed($i, false);
                } else {
                    $stage_ro_wb_reset = false;
                }
            }
            $pipeline = $this->ExecutionPipelines->getPipeline($i);
            $pipelineDrop = $pipeline->reset($pID);
            for ($j = 0; $j < $pipelineDrop->getLengthDeleted(); $j++) {
                $cmd = $pipelineDrop->getCommandDeleted($j);
                $this->i_programmRun[$cmd->getProcessId()] = $cmd->getStats();
                $this->i_instructionPool->FreeRegisters($cmd);
            }
            $this->ExecutionPipelines->InsertPipeline($i, $pipeline);
        }
        $resultResetReorder = $this->i_ReorderBuffer->reset($pID, $this->processID, $this->states->takt);
        for ($j = 0; $j < $resultResetReorder->getLengthDeleted(); $j++) {
            $cmd = $resultResetReorder->getCommandDeleted($j);
            $this->i_instructionPool->FreeRegisters($cmd);
            $this->i_programmRun[$cmd->getProcessId()] = $cmd->getStats();
        }
        $this->Stage_if_id->setLocked(false);
        $this->Stage_id_ip->setLocked(false);
        $ipReset = $this->i_instructionPool->reset($pID, $this->states->takt);
        foreach (array_keys($ipReset) as $key) {
            $this->i_programmRun[$key] = $ipReset[$key];
        }
        $this->states->resetOoO($pID);
        if ($stage_ro_wb_reset) {
            $this->Stage_ro_wb->setLocked(false);
        } else {
            $this->Stage_ro_wb->setLocked(true);
        }
        $this->states->counter_add_shift = $this->states->counter_add_shift + 1;
        $this->states->hold_loop = true;
    }
}