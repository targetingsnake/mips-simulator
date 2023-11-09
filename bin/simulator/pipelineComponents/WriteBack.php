<?php

/**
 * represents write back
 */
class WriteBack extends Component
{

    /**
     * @var array holds all register value which will be written back
     */
    private array $i_writeback_buffer;

    /**
     * initialises WritebackBuffer
     */
    public function __construct()
    {
        parent::__construct("WB");
        $this->i_writeback_buffer = array();
    }

    /**
     * add a calculated value to buffer
     * @param int $processID identifier of command run
     * @param Register $reg register with calculated value
     */
    public function addToBuffer(int $processID, Register $reg): void
    {
        $this->i_writeback_buffer[$processID] = $reg;
    }

    /**
     * write back a calculated value
     * @param int $processID process identifier of command
     * @return Register|null return register to write it back to memory
     */
    public function WriteBack(int $processID)
    {
        if (!isset($this->i_writeback_buffer[$processID])) {
            return new Register("\$zero",);
        }
        $register = $this->i_writeback_buffer[$processID];
        unset($this->i_writeback_buffer[$processID]);
        return $register;
    }

    /**
     * returns register of command
     * @param int $processID process identifier of command
     * @return Register register to be returned
     */
    public function getRegisterOfCommand(int $processID): Register
    {
        if (!isset($this->i_writeback_buffer[$processID])) {
            return new Register("\$zero",);
        }
        return $this->i_writeback_buffer[$processID];
    }
}