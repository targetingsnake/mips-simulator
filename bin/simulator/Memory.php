<?php

/**
 * represents memory
 */
class Memory
{
    /**
     * @var array array of memory cells
     */
    private array $i_MemoryCells;

    /**
     * Initializes memory
     */
    public function __construct()
    {
        $this->i_MemoryCells = array();
        for ($i = 0; $i < 32; $i++) {
            $memoryName = $i * 4;
            $this->i_MemoryCells[$memoryName] = new MemoryCell($memoryName, 0);
        }
    }

    /**
     * gets value of a memory cell
     * @param int $address address of memory cell
     * @return int value of memory cell
     */
    public function getValue(int $address): int
    {
        return $this->i_MemoryCells[$address]->getValue();
    }

    /**
     * sets value of memory cell
     * @param int $address address of memory cell
     * @param int $value value of memory cell
     */
    public function setValue(int $address, int $value)
    {
        return $this->i_MemoryCells[$address]->setValue($value);
    }

    /**
     * gets a memory map for log
     * @return array log of current memory
     */
    public function getMemoryComplete(): array
    {
        $result = array();
        $keys = array_keys($this->i_MemoryCells);
        foreach ($keys as $key) {
            if ($this->i_MemoryCells[$key]->getValue() == 0) {
                continue;
            }
            $result[$key] = array(
                "adr" => $key,
                "val" => $this->i_MemoryCells[$key]->getValue()
            );
        }
        return $result;
    }
}