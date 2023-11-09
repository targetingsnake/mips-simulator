<?php

/**
 * represents a register
 */
class Register
{
    /**
     * @var bool defines if register is locked
     */
    private bool $i_locked;

    /**
     * @var int name of register
     */
    private string $i_name;

    /**
     * @var int value of register
     */
    private int $i_value;

    /**
     * initialises a register
     * @param string $name name of Register
     */
    public function __construct(string $name)
    {
        $this->i_locked = false;
        $this->i_value = 0;
        $this->i_name = $name;
    }

    /**
     * Returns name of register as number
     * @return int name of register
     */
    public function getName() : string {
        return $this->i_name;
    }

    /**
     * Returns current state of register
     * @return bool lock state of register
     */
    public function getLockState() : bool {
        return $this->i_locked;
    }

    /**
     * set lock state of register
     * @param bool $state new lock state of register
     */
    public function setLockState(bool $state) :void {
        $this->i_locked = $state;
    }

    /**
     * Returns value of Register
     * @return int integer value of register
     */
    public function getValue() : int {
        return $this->i_value;
    }

    /**
     * sets value of register
     * @param int $new_value new value of register
     */
    public function setValue(int $new_value) : void {
        if ($this->i_locked) {
            return;
        }
        $this->i_value = $new_value;
    }
}