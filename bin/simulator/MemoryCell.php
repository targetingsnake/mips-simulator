<?php

class MemoryCell
{
    /**
     * @var int $i_name name of cell as numeric
     */
    private int $i_name;
    /**
     * @var int $i_value value of memory cell
     */
    private int $i_value;

    /**
     * initialises values
     * @param int $name name of this cell
     * @param int $value initial value of memory cell
     */
    public function __construct(int $name, int $value)
    {
        $this->i_name = $name;
        $this->i_value = $value;
    }

    public function getName() : int {
        return $this->i_name;
    }

    public function getValue() : int {
        return $this->i_value;
    }

    public function setValue(int $new_value) : void {
        $this->i_value = $new_value;
    }
}