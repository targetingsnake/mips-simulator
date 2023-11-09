<?php

/**
 * reprsents instruction pool
 */
class InstructionPool extends InstructionSchedulerStub
{
    /**
     * @var array array with buffered commands
     */
    private array $pool;

    /**
     * @var Command command which will be executed next
     */
    private Command $out;

    /**
     * @var bool defines if there is a next command
     */
    private bool $out_lock;

    /**
     * Initializes variables and parent class
     */
    public function __construct()
    {
        parent::__construct("IP");
        $this->pool = array();
        $this->out_lock = false;
        $this->out = new Command("", 0);
    }

    /**
     * adds a command to the pool
     * @param Command $cmd command to be added
     */
    public function addCommandToPool(Command $cmd): void
    {
        $this->pool[$cmd->getProcessId()] = $cmd;
    }

    /**
     * checks if there is command that can be executed
     * @return bool true if there is a command to execute next
     */
    public function hasExecutable(): bool
    {
        if (!$this->out_lock) {
            $this->setNext();
        }
        return $this->out_lock;
    }

    /**
     * returns executable command
     * @return Command command which will be executed next
     */
    public function getExecutable(): Command
    {
        $this->setRegistersForCommand($this->out, true);
        $this->out_lock = false;
        return $this->out;
    }

    /**
     * add entry to every command in buffer that it has been in this stage in this cycle
     * @param int $takt number of tact
     */
    public function processCmds(int $takt)
    {
        $keys = array_keys($this->pool);
        foreach ($keys as $key) {
            $this->pool[$key] = parent::processCmd($this->pool[$key], $takt);
        }
    }

    /**
     * gets next command out of buffer
     */
    private function setNext()
    {
        if ($this->out_lock) {
            return;
        }
        $keys = array_keys($this->pool);
        $oldestPID = PHP_INT_MAX;
        foreach ($keys as $key) {
            if ($this->isCommandRunnable($this->pool[$key])) {
                if ($this->checkReadBeforeWrite($key)) {
                    if ($oldestPID > $key) {
                        $oldestPID = $key;
                    } else {
                        $oldestPID = $key;
                    }
                }
            }
        }
        if ($oldestPID != PHP_INT_MAX) {
            $this->out_lock = true;
            $this->out = $this->pool[$oldestPID];
            unset($this->pool[$oldestPID]);
        }
    }

    /**
     * checks if a register is read before it is written
     * @param $processID int process identifier of command
     * @return bool result of check
     */
    private function checkReadBeforeWrite(int $processID): bool
    {
        $check = true;
        $keys = array_keys($this->pool);
        foreach ($keys as $key) {
            if ($processID > $key) {
                if ($this->checkCommandReadBeforeWrite($this->pool[$processID], $this->pool[$key])) {
                    $check = false;
                }
            }
        }
        return $check;
    }

    /**
     * checks two commands if one has an effect on the other
     * @param Command $checked command which is checked
     * @param Command $checkedAgainst command which would be executed prior
     * @return bool true if there will be an effect
     */
    private function checkCommandReadBeforeWrite(Command $checked, Command $checkedAgainst): bool
    {
        $result = false;
        $args = $checked->getArguments();
        switch ($checked->getCommandType()) {
            case 0:
                $result = in_array($args[1], $this->getReadRegisters($checkedAgainst)) ||
                    in_array($args[2], $this->getWriteRegister($checkedAgainst));
                break;
            case 1:
                $result = false;
                break;
            case 2:
                $result = in_array($args[1], $this->getReadRegisters($checkedAgainst)) ||
                    in_array($args[2], $this->getWriteRegister($checkedAgainst)) ||
                    in_array($args[3], $this->getWriteRegister($checkedAgainst));
                break;
            case 3:
                if ($checked->getCommand() == 'sw') {
                    $result = in_array($args[1], $this->getWriteRegister($checkedAgainst));
                } else if ($checked->getCommand() == 'lw') {
                    $result = in_array($args[1], $this->getReadRegisters($checkedAgainst));
                }
                break;
            case 5:
                $result = in_array($args[2], $this->getWriteRegister($checkedAgainst));
                break;
            case 6:
                $result = in_array($args[1], $this->getWriteRegister($checkedAgainst)) ||
                    in_array($args[2], $this->getWriteRegister($checkedAgainst));
                break;
        }
        return $result;
    }

    /**
     * returns registers which will be read by a command
     * @param Command $cmd command of which the registers for reading are requested
     * @return array array with names of registers
     */
    private function getReadRegisters(Command $cmd): array
    {
        $result = array();
        $args = $cmd->getArguments();
        switch ($cmd->getCommandType()) {
            case 0:
                $result[] = $args[2];
                break;
            case 1:
                $result = array();
                break;
            case 2:
                $result[] = $args[2];
                $result[] = $args[3];
                break;
            case 3:
                if ($cmd->getCommand() == 'sw') {
                    $result[] = $args[1];
                }
                break;
            case 5:
                $result[] = $args[1];
                break;
            case 6:
                $result[] = $args[1];
                $result[] = $args[2];
                break;
        }
        return $result;
    }

    /**
     * returns registers which will be written by a command
     * @param Command $cmd command of which the registers for writing are requested
     * @return array array with names of registers
     */
    private function getWriteRegister(Command $cmd): array
    {
        $result = array();
        $args = $cmd->getArguments();
        switch ($cmd->getCommandType()) {
            case 0:
                $result[] = $args[1];
                break;
            case 1:
                $result = array();
                break;
            case 2:
                $result[] = $args[1];
                break;
            case 3:
                if ($cmd->getCommand() == 'lw') {
                    $result[] = $args[1];
                }
                break;
            case 5:
            case 6:
                $result = array();
                break;
        }
        return $result;
    }

    /**
     * checks if commands are in buffer
     * @return bool true if command are in buffer
     */
    public function hasCommands(): bool
    {
        return count($this->pool) > 0;
    }

    /**
     * unlocks registers
     * @param Command $cmd command which registers should be unlocked
     */
    public function FreeRegisters(Command $cmd): void
    {
        $this->setRegistersForCommand($cmd, false);
    }

    public function reset(int $processID, int $takt): array
    {
        $keys = array_keys($this->pool);
        $log = array();
        foreach ($keys as $key) {
            if ($key >= $processID) {
                $cmd = $this->getCommandByKey($key);
                $cmd->log_history("FL", $takt + 1);
                $log[$cmd->getProcessId()] = $cmd->getStats();
                unset($this->pool[$key]);
            }
        }
        if ($this->out_lock) {
            if ($this->out->getProcessId() >= $processID) {
                $log[$this->out->getProcessId()] = $this->out->getStats();
                $this->out_lock = false;
            }
        }
        return $log;
    }

    /**
     * returns command by its process identifier
     * @param int $key process identifier of command
     * @return Command searched command
     */
    private function getCommandByKey(int $key) : Command
    {
        return $this->pool[$key];
    }
}