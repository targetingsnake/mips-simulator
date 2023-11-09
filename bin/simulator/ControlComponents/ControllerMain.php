<?php

/**
 * control class for no pipeline
 */
class ControllerMain
{
    /**
     * @var ProgramMemory Programm memory of Simulation
     */
    protected ProgramMemory $i_programmMemory;

    /**
     * @var Registers Registers of Simulation
     */
    protected Registers $i_registers;

    /**
     * @var Memory memory of Programm
     */
    protected Memory $i_memory;

    /**
     * @var InstructionFetch Instruction fetch step of the simulation
     */
    protected InstructionFetch $i_instructionFetch;

    /**
     * @var InstructionDecode Instruction decode step of the simulation
     */
    protected InstructionDecode $i_instructionDecode;

    /**
     * @var OperandFetch Operand fetch step of the simulation
     */
    protected OperandFetch $i_operandFetch;

    /**
     * @var Execution Execution step of the simulation
     */
    protected Execution $i_execution;

    /**
     * @var WriteBack WriteBack step of the simulation
     */
    protected WriteBack $i_writeBack;

    /**
     * @var array collects all run commands and history
     */
    protected array $i_programmRun;

    /**
     * @var array collects memory for each takt
     */
    protected array $i_MemoryHistory;

    /**
     * @var array collects data of branch prediction for each takt
     */
    protected array $i_BranchHistory;

    /**
     * @var array collects register for eacht takt
     */
    protected array $i_RegisterHistory;

    /**
     * @var int amount of takte the programm has needed to run
     */
    protected int $i_runTakt;

    /**
     * @var int represents current takt of cpu
     */
    protected int $takt;

    /**
     * @var int for each executed Command as unique identifier
     */
    protected int $processID;

    /**
     * @var int current Programm pointer
     */
    protected int $ProgrammPointer;

    /**
     * @var bool sets if sim finished with FLush of pipeline
     */
    protected bool $abortWithFL;

    protected bool $error;


    /**
     * Main run class for unpipelined run
     * @param string $programmCode programmcode as text
     */
    public function __construct(string $programmCode)
    {
        $cleanProgramm = InputChecker::removeEmptyLinesAndCommentLines($programmCode);
        $this->i_programmMemory = new ProgramMemory($cleanProgramm);
        $this->i_registers = new Registers();
        $this->i_memory = new Memory();
        $this->i_instructionFetch = new InstructionFetch();
        $this->i_instructionDecode = new InstructionDecode();
        $this->i_operandFetch = new OperandFetch();
        $this->i_execution = new Execution();
        $this->i_writeBack = new WriteBack();
        $this->i_programmRun = array();
        $this->i_MemoryHistory = array();
        $this->i_runTakt = 0;
        $this->takt = 0;
        $this->processID = 0;
        $this->ProgrammPointer = 0;
        $this->abortWithFL = false;
        $this->i_BranchHistory = array();
        $this->error = false;
        for ($i = 0; $i < $this->i_programmMemory->getProgrammLength(); $i++) {
            if ($this->i_programmMemory->getCommand($i)->getCommandType() == -1) {
                $this->error = true;
            }
        }
    }

    /**
     * Returns Memory of the simulator at this point of time
     * @return array returns the complete Memory of the Simulation
     */
    public function getProgrammMemory(): array
    {
        return $this->i_programmMemory->getProgrammMemory();
    }

    /**
     * Returns Registers of the simulator at this point of time
     * @return array returns complete Registers
     */
    protected function getRegisters(): array
    {
        return $this->i_registers->getCompleteContent();
    }

    /**
     * returns complete memory
     * @return array returns complete memory
     */
    protected function getMemory(): array
    {
        return $this->i_memory->getMemoryComplete();
    }

    /**
     * executes Programm;
     */
    public function run(): void
    {
        $commandCount = $this->i_programmMemory->getProgrammLength();
        do {
            // fetch command
            $cmd = clone $this->i_programmMemory->getCommand($this->ProgrammPointer);
            $cmd->setProcessId($this->processID);

            // process instruction fetch
            $cmd = $this->i_instructionFetch->processCmd($cmd, $this->takt);
            $this->logMemoryAndRegister();
            $this->takt = $this->takt + 1;

            //proceass instruction decode
            $cmd = $this->i_instructionDecode->processCmd($cmd, $this->takt);
            $this->logMemoryAndRegister();
            $this->takt = $this->takt + 1;

            //process operand fetch
            $cmd = $this->i_operandFetch->processCmd($cmd, $this->takt);
            $this->logMemoryAndRegister();
            $this->takt = $this->takt + 1;

            //process instruction execution
            $this->ProgrammPointer = $this->ProgrammPointer + 1;

            if ($cmd->getExecutionTimeRemaining() > 1) {
                do {
                    $this->i_MemoryHistory[$this->takt] = $this->getMemory();
                    $this->i_RegisterHistory[$this->takt] = $this->getRegisters();
                    $cmd = $this->i_execution->processCmd($cmd, $this->takt);
                    $this->takt = $this->takt + 1;
                    $this->logMemoryAndRegister();
                    $cmd->decreaseExecutionTime();
                } while ($cmd->getExecutionTimeRemaining() > 1);
            }

            $executed = $this->execute($cmd, $this->ProgrammPointer);
            $cmd = $this->i_execution->processCmd($cmd, $this->takt);
            $this->ProgrammPointer = $executed['pgm'];
            $this->logMemoryAndRegister();
            $this->takt = $this->takt + 1;

            // execute writeback
            $cmd = $this->i_writeBack->processCmd($cmd, $this->takt);
            $register = $this->i_writeBack->WriteBack($this->processID);
            if ($register != null) {
                $this->i_registers->setRegisterContent($register);
            }
            $this->logMemoryAndRegister($this->takt);
            $this->i_programmRun[$this->processID] = $cmd->getStats();
            $this->i_runTakt = $this->takt;
            $this->takt = $this->takt + 1;
            $this->processID = $this->processID + 1;
        } while ($this->ProgrammPointer < $commandCount);
    }

    /**
     * return all important run facts of programm
     * @return array structured result
     */
    public function getRunStats(): array
    {
        $progRun = array();
        for ($i = 0; $i < count($this->i_programmRun); $i++) {
            if (isset($this->i_programmRun[$i])) {
                $progRun[] = $this->i_programmRun[$i];
            }
        }
        if ($this->abortWithFL) {
            $this->i_RegisterHistory[$this->i_runTakt + 1] = $this->i_RegisterHistory[$this->i_runTakt];
            $this->i_MemoryHistory[$this->i_runTakt + 1] = $this->i_MemoryHistory[$this->i_runTakt];
            $this->i_BranchHistory[$this->i_runTakt + 1] = $this->i_BranchHistory[$this->i_runTakt];
        }
        $result = array(
            "memory" => $this->i_MemoryHistory,
            "register" => $this->i_RegisterHistory,
            "programm" => $progRun,
            "bht" => $this->i_BranchHistory,
            "counter" => $this->abortWithFL ? $this->i_runTakt + 1 : $this->i_runTakt
        );
        return $result;
    }

    /**
     * logs memory and register state after current takt
     * @param int $this- >takt takt which was executed
     */
    protected function logMemoryAndRegister(): void
    {
        $this->i_MemoryHistory[$this->takt] = $this->getMemory();
        $this->i_RegisterHistory[$this->takt] = $this->getRegisters();
        $this->i_BranchHistory[$this->takt] = array();
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
        if (is_null($registers)) {
            $registers = $this->i_registers;
        }
        $jmp = false;
        if ($cmd->getCommandType() == 0) { //Type I
            $register = $registers->getRegisterContent($cmd->getArguments()[2]);
            $result = $this->i_execution->ExecuteTypeI($cmd, $register->getValue());
            $this->i_writeBack->addToBuffer($cmd->getProcessId(), $result);
        } else if ($cmd->getCommandType() == 1) { // Type J
            $programmPointer = $this->i_programmMemory->getLabel($this->i_execution->ExecuteTypeJ($cmd));
            $jmp = true;
        } else if ($cmd->getCommandType() == 2) { // Type R
            $register1 = $registers->getRegisterContent($cmd->getArguments()[2]);
            $register2 = $registers->getRegisterContent($cmd->getArguments()[3]);
            $result = $this->i_execution->ExecuteTypeR($cmd, $register1->getValue(), $register2->getValue());
            $this->i_writeBack->addToBuffer($cmd->getProcessId(), $result);
        } else if ($cmd->getCommandType() == 3) { // Type Memory Command
            $memoryCellAddr = intval($cmd->getArguments()[2]);
            switch ($cmd->getCommand()) {
                case 'lw':
                    $register = new Register($cmd->getArguments()[1]);
                    $register->setLockState(false);
                    $register->setValue($this->i_memory->getValue($memoryCellAddr));
                    $this->i_writeBack->addToBuffer($cmd->getProcessId(), $register);
                    break;
                case 'sw':
                    $register = $registers->getRegisterContent($cmd->getArguments()[1])->getValue();
                    $this->i_memory->setValue($memoryCellAddr, $register);
                    break;
            }
        } else if ($cmd->getCommandType() == 5) { // Type 2 parameters Jump
            $register = $registers->getRegisterContent($cmd->getArguments()[1])->getValue();
            $result = $this->i_execution->ExecuteJump2Params($cmd, $register);
            if ($result['jump']) {
                $programmPointer = $this->i_programmMemory->getLabel($result['label']);
                $jmp = true;
            }
        } else if ($cmd->getCommandType() == 6) { // Type 3 parameters Jump
            $register1 = $registers->getRegisterContent($cmd->getArguments()[1])->getValue();
            $register2 = $registers->getRegisterContent($cmd->getArguments()[2])->getValue();
            $result = $this->i_execution->ExecuteJump3Params($cmd, $register1, $register2);
            if ($result['jump']) {
                $programmPointer = $this->i_programmMemory->getLabel($result['label']);
                $jmp = true;
            }
        }
        return array(
            "cmd" => $cmd,
            "pgm" => $programmPointer,
            "jmp" => $jmp
        );
    }

    /**
     * @return bool checks if an error occurred
     */
    public function checkError(): bool
    {
        return $this->error;
    }
}