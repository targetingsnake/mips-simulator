<?php

/**
 * simulated shadow register-set
 */
class ShadowRegisters
{
    /**
     * @var Registers simulated registers-set
     */
    private Registers $Registers;

    /**
     * @var array saves of the registers
     */
    private array $save;

    /**
     * initializes variables
     */
    public function __construct()
    {
        $this->Registers = new Registers();
        $this->save = array();
    }

    /**
     * @return Registers returns current register set
     */
    public function getRegisters(): Registers
    {
        return $this->Registers;
    }

    /**
     * sets a register and also updates saves if necessary
     * @param int $processID process id of command which updates a register
     * @param Register $reg updates register
     */
    public function setRegisters(int $processID, Register $reg): void
    {
        if (count($this->save) > 0) {
            $keys = array_keys($this->save);
            foreach ($keys as $key) {
                if ($key > $processID) {
                    $sve = $this->getSave($key);
                    $sve->setRegisterContent($reg);
                    $this->save[$key] = clone ($sve);
                }
            }
        }
        $this->Registers->setRegisterContent($reg);
    }

    /**
     * returns a save of registers
     * @param int $key identifier of save
     * @return Registers set of registers
     */
    private function getSave(int $key): Registers
    {
        return $this->save[$key];
    }

    /**
     * makes a save of register set
     * @param int $key identifier of save
     */
    public function makeSave(int $key): void
    {
        $this->save[$key] = clone ($this->Registers);
    }

    /**
     * reset register set to a certain save
     * @param int $processID process id of command which resets to a save
     */
    public function Reset(int $processID)
    {
        $this->Registers = $this->getSave($processID);
    }

    /**
     * deletes a certain save
     * @param $processID int jump command which is done with execution
     */
    public function deleteSave(int $processID)
    {
        unset($this->save[$processID]);
    }
}