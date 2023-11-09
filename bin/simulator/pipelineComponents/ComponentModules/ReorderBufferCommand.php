<?php

/**
 * Command for special buffer
 */
class ReorderBufferCommand
{
    /**
     * @var Register result of command as register
     */
    private Register $i_reg;

    /**
     * @var Command command
     */
    private Command $i_cmd;

    /**
     * @var bool defines if there is a result
     */
    private bool $i_reg_set;

    /**
     * initializes variables
     * @param Register $reg result as a register
     * @param Command $cmd command
     * @param bool $reg_set true if command has a result
     */
    public function __construct(Register $reg, Command $cmd, bool $reg_set)
    {
        $this->i_cmd = $cmd;
        $this->i_reg = $reg;
        $this->i_reg_set = $reg_set;
    }

    /**
     * returns register
     * @return Register register with result
     */
    public function getRegister(): Register
    {
        return $this->i_reg;
    }

    /**
     * returns command
     * @return Command command
     */
    public function getBasicCommand(): Command
    {
        return $this->i_cmd;
    }

    /**
     * sets command
     * @param Command $cmd command with updates
     */
    public function setBasicCommand(Command $cmd) : void
    {
        $this->i_cmd = $cmd;
    }

    /**
     * checks if command has result
     * @return bool true if there is a result
     */
    public function isRegisterSet() : bool
    {
        return $this->i_reg_set;
    }
}