<?php

/**
 * stub class for instruction scheduler and instruction pool
 */
class InstructionSchedulerStub extends Component
{
    /**
     * @var array array of state for registers which will be read
     */
    private array $readBuffer;

    /**
     * @var array array of state for registers which will be written
     */
    private array $writeBuffer;

    /**
     * initializes variables and parent class
     * @param string $name name of component
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        foreach (array_flip(Registers::getNameToNumber()) as $name) {
            $this->readBuffer[$name] = false;
            $this->writeBuffer[$name] = false;
        }
    }

    /**
     * checks if command is runable
     * Types:
     * 0 - Type I
     * 1 - Type J
     * 2 - Type R
     * 3 - Memory commands
     * 4 - Pseudo commands
     * 5 - JumpTypes 2 arguments
     * 6 - JumpTypes 3 arguments
     * @param Command $cmd command which will be checked
     * @return bool return true if command is runable
     */
    protected function isCommandRunnable(Command $cmd): bool
    {
        switch ($cmd->getCommandType()) {
            case 0:
                return $this->isCommandRunnableTypeI($cmd);
            case 1:
                return true;
            case 2:
                return $this->isCommandRunnableTypeR($cmd);
            case 3:
                return $this->isCommandRunnableTypeMemoryCmd($cmd);
            case 5:
                return $this->isCommandRunnableTypeJump2($cmd);
            case 6:
                return $this->isCommandRunnableTypeJump3($cmd);
        }
        return false;
    }

    /**
     * checks commands of type I
     * @param Command $cmd Command which is checked
     * @return bool true if command is runnable
     */
    protected function isCommandRunnableTypeI(Command $cmd): bool
    {
        $args = $cmd->getArguments();
        $runnable = $this->readBuffer[$args[1]] || $this->writeBuffer[$args[2]];
        return $runnable == false;
    }

    /**
     * checks commands of type R
     * @param Command $cmd Command which is checked
     * @return bool true if command is runnable
     */
    protected function isCommandRunnableTypeR(Command $cmd): bool
    {
        $args = $cmd->getArguments();
        $runnable = $this->readBuffer[$args[1]] || $this->writeBuffer[$args[2]] || $this->writeBuffer[$args[3]];
        return $runnable == false;
    }

    /**
     * checks commands of type jump 2
     * @param Command $cmd Command which is checked
     * @return bool true if command is runnable
     */
    protected function isCommandRunnableTypeJump2(Command $cmd): bool
    {
        return $this->writeBuffer[$cmd->getArguments()[1]] == false;
    }

    /**
     * checks commands of type jump 3
     * @param Command $cmd Command which is checked
     * @return bool true if command is runnable
     */
    protected function isCommandRunnableTypeJump3(Command $cmd): bool
    {
        $args = $cmd->getArguments();
        $runnable = $this->writeBuffer[$args[1]] || $this->writeBuffer[$args[2]];
        return $runnable == false;
    }

    /**
     * checks memory commands
     * @param Command $cmd Command which is checked
     * @return bool true if command is runnable
     */
    protected function isCommandRunnableTypeMemoryCmd(Command $cmd): bool
    {
        switch ($cmd->getCommand()) {
            case 'lw':
                $runnable = $this->readBuffer[$cmd->getArguments()[1]];
                return $runnable == false;
            case 'sw':
                $runnable = $this->writeBuffer[$cmd->getArguments()[1]];
                return $runnable == false;
        }
        return false;
    }

    /**
     * sets registers as used for command type I
     * @param Command $cmd Command for which Registers are set used
     * @param bool $state sets if registers are used or if command has finished and registers are given free
     */
    protected function setCommandRegisterStateTypeI(Command $cmd, bool $state): void
    {
        $args = $cmd->getArguments();
        $this->writeBuffer[$args[1]] = $state;
        $this->readBuffer[$args[2]] = $state;
    }

    /**
     * sets registers as used for command type R
     * @param Command $cmd Command for which Registers are set used
     * @param bool $state sets if registers are used or if command has finished and registers are given free
     */
    protected function setCommandRegisterStateTypeR(Command $cmd, bool $state): void
    {
        $args = $cmd->getArguments();
        $this->writeBuffer[$args[1]] = $state;
        $this->readBuffer[$args[2]] = $state;
        $this->readBuffer[$args[3]] = $state;
    }

    /**
     * sets registers as used for command type jump 2
     * @param Command $cmd Command for which Registers are set used
     * @param bool $state sets if registers are used or if command has finished and registers are given free
     */
    protected function setCommandRegisterStateTypeJump2(Command $cmd, bool $state): void
    {
        $this->readBuffer[$cmd->getArguments()[1]] = $state;
    }

    /**
     * sets registers as used for command type jump 3
     * @param Command $cmd Command for which Registers are set used
     * @param bool $state sets if registers are used or if command has finished and registers are given free
     */
    protected function setCommandRegisterStateTypeJump3(Command $cmd, bool $state): void
    {
        $args = $cmd->getArguments();
        $this->readBuffer[$args[1]] = $state;
        $this->readBuffer[$args[2]] = $state;
    }

    /**
     * sets registers as used for memory commands
     * @param Command $cmd Command for which Registers are set used
     * @param bool $state sets if registers are used or if command has finished and registers are given free
     */
    protected function setCommandRegisterStateTypeMemoryCmd(Command $cmd, bool $state): void
    {
        switch ($cmd->getCommand()) {
            case 'lw':
                $this->writeBuffer[$cmd->getArguments()[1]] = $state;
                break;
            case 'sw':
                $this->readBuffer[$cmd->getArguments()[1]] = $state;
                break;
        }
    }

    /**
     * sets registers as used for command
     * @param Command $cmd Command for which Registers are set used
     * @param bool $state sets if registers are used or if command has finished and registers are given free
     */
    protected function setRegistersForCommand(Command $cmd, bool $state): void
    {
        switch ($cmd->getCommandType()) {
            case 0:
                $this->setCommandRegisterStateTypeI($cmd, $state);
                break;
            case 1:
                break;
            case 2:
                $this->setCommandRegisterStateTypeR($cmd, $state);
                break;
            case 3:
                $this->setCommandRegisterStateTypeMemoryCmd($cmd, $state);
                break;
            case 5:
                $this->setCommandRegisterStateTypeJump2($cmd, $state);
                break;
            case 6:
                $this->setCommandRegisterStateTypeJump3($cmd, $state);
                break;
        }
    }

    /**
     * sets states for a certain Register for read lock
     * @param string $Register name of register
     * @param bool $state state of register
     */
    protected function setRegisterStateRead(string $Register, bool $state): void
    {
        $this->readBuffer[$Register] = $state;
    }

    /**
     * sets states for a certain Register for write lock
     * @param string $Register name of register
     * @param bool $state state of register
     */
    protected function setRegisterStateWrite(string $Register, bool $state): void
    {
        $this->writeBuffer[$Register] = $state;
    }
}