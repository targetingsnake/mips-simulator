<?php

/**
 * represents reorder buffer
 */
class ReorderBuffer extends Component
{
    /**
     * @var int process identifier of last executed command in this stage
     */
    private int $lastProcessID;

    /**
     * @var int next process identifier after reset
     */
    private int $newProcessID;

    /**
     * @var int process identifier at which reset will be performed
     */
    private int $resetID;

    /**
     * @var bool defines if there is a reset to do
     */
    private bool $resetSwitch;

    /**
     * @var array buffer of commands and their result
     */
    private array $Buffer;

    /**
     * Initializes variables and parent class
     */
    public function __construct()
    {
        parent::__construct("RB");
        $this->lastProcessID = 0;
        $this->newProcessID = 0;
        $this->resetID = 0;
        $this->resetSwitch = false;
        $this->Buffer = array();
    }

    /**
     * adds a command to the buffer
     * @param ReorderBufferCommand $ROcmd reorder command to add
     */
    public function addCommand(ReorderBufferCommand $ROcmd)
    {
        $this->Buffer[$ROcmd->getBasicCommand()->getProcessId()] = $ROcmd;
    }

    /**
     * checks if there is a command to return
     * @return bool true if a command can be returned
     */
    public function checkNextReady(): bool
    {
        if ($this->resetSwitch) {
            if ($this->lastProcessID >= $this->resetID) {
                $this->resetSwitch = false;
                $this->lastProcessID = $this->newProcessID;
            }
        }
        return isset($this->Buffer[$this->lastProcessID]);
    }

    /**
     * returns next command
     * @return ReorderBufferCommand command which is returned
     */
    public function getNextCommand(): ReorderBufferCommand
    {
        $out = $this->Buffer[$this->lastProcessID];
        unset($this->Buffer[$this->lastProcessID]);
        $this->lastProcessID = $this->lastProcessID + 1;
        return $out;
    }

    /**
     * logs current stage to each command in buffer
     * @param int $takt current tact
     */
    public function processCmds(int $takt): void
    {
        $keys = array_keys($this->Buffer);
        foreach ($keys as $key) {
            $this->Buffer[$key]->setBasicCommand($this->processCmd($this->Buffer[$key]->getBasicCommand(), $takt));
        }
    }

    /**
     * checks if there are commands in buffer
     * @return bool true if there are commands in buffer
     */
    public function hasCommands(): bool
    {
        return count($this->Buffer) > 0;
    }

    /**
     * reset reorder buffer
     * @param int $ProcessID process identifier of last executed jump command
     * @param int $nextProcessID process identifier of next valid command
     * @param int $takt current tact
     * @return ResetPipelineResult removed commands
     */
    public function reset(int $ProcessID, int $nextProcessID, int $takt): ResetPipelineResult
    {
        $keys = array_keys($this->Buffer);
        $log = new ResetPipelineResult();
        foreach ($keys as $key) {
            if ($key >= $ProcessID) {
                $cmd = $this->getCommandByKey($key)->getBasicCommand();
                $cmd->log_history("FL", $takt + 1);
                $log->addCommandDeleted($cmd);
                unset($this->Buffer[$key]);
            }
        }
        if ($this->lastProcessID + 1 != $nextProcessID) {
            $this->newProcessID = $nextProcessID;
            $this->resetSwitch = true;
            $this->resetID = $ProcessID;
        } else {
            $this->lastProcessID = $nextProcessID;
        }
        return $log;
    }

    /**
     * returns certain command from buffer
     * @param int $key process identifier of command
     * @return ReorderBufferCommand searched command
     */
    private function getCommandByKey (int $key) : ReorderBufferCommand
    {
        return $this->Buffer[$key];
    }
}