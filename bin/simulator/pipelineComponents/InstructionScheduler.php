<?php

/**
 * represents instruction scheduler
 */
class InstructionScheduler extends InstructionSchedulerStub
{
    /**
     * initializes an instance of Instruction Scheduler and initializes parent class
     */
    public function __construct()
    {
        parent::__construct("IS");
    }

    /**
     * check if a command is runable
     * @param Command $cmd command which has to be checked
     * @return bool true if it is runable
     */
    public function isCommandRunnable(Command $cmd): bool
    {
        return parent::isCommandRunnable($cmd);
    }

    /**
     * locks or unlocks registers of command
     * @param Command $cmd command which action should be performed for
     * @param bool $state state which will be set to registers
     */
    public function setRegistersForCommand(Command $cmd, bool $state): void
    {
        parent::setRegistersForCommand($cmd, $state);
    }

    /**
     * locks or unlocks read registers of command
     * @param string $Register register which should be locked or unlocked
     * @param bool $state state which will be set to registers
     */
    public function setRegisterStateRead(string $Register, bool $state): void
    {
        parent::setRegisterStateRead($Register, $state);
    }

    /**
     * locks or unlocks write registers of command
     * @param string $Register register which should be locked or unlocked
     * @param bool $state state which will be set to registers
     */
    public function setRegisterStateWrite(string $Register, bool $state): void
    {
        parent::setRegisterStateWrite($Register, $state);
    }

    /**
     * resets Instruction scheduler
     */
    public function reset(): void
    {
        foreach (array_flip(Registers::getNameToNumber()) as $name) {
            parent::setRegisterStateRead($name, false);
            parent::setRegisterStateWrite($name, false);;
        }
    }
}