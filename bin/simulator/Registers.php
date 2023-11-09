<?php

/**
 * represents a register set
 */
class Registers
{
    /**
     * @var array array with all register values
     */
    private array $i_registers;

    /**
     * @var array assoziate mapping of name to number
     */
    private static array $i_assoziate_register_name = array(
        "\$zero" => 0,
        "\$at" => 1,
        "\$v0" => 2,
        "\$v1" => 3,
        "\$a0" => 4,
        "\$a1" => 5,
        "\$a2" => 6,
        "\$a3" => 7,
        "\$t0" => 8,
        "\$t1" => 9,
        "\$t2" => 10,
        "\$t3" => 11,
        "\$t4" => 12,
        "\$t5" => 13,
        "\$t6" => 14,
        "\$t7" => 15,
        "\$s0" => 16,
        "\$s1" => 17,
        "\$s2" => 18,
        "\$s3" => 19,
        "\$s4" => 20,
        "\$s5" => 21,
        "\$s6" => 22,
        "\$s7" => 23,
        "\$t8" => 24,
        "\$t9" => 25,
        "\$k0" => 26,
        "\$k1" => 27,
        "\$gp" => 28,
        "\$sp" => 29,
        "\$fp" => 30,
        "\$ra" => 31
    );

    /**
     * initiates registers
     */
    public function __construct()
    {
        for ($i = 0; $i < 32; $i++) {
            $this->i_registers[$i] = new Register(array_flip(self::$i_assoziate_register_name)[$i]);
        }
    }

    /**
     * returns array of matching names and registers
     * @return array list of registers
     */
    public static function getNameToNumber(): array
    {
        return self::$i_assoziate_register_name;
    }

    /**
     * reads register value
     * @param string $name name of register to read e.g. $at
     * @return Register return value of register
     */
    public function getRegisterContent(string $name): Register
    {
        return $this->i_registers[self::$i_assoziate_register_name[$name]];
    }

    /**
     * sets the content of a specific register
     * @param string $name name of the register which gets updated
     */
    public function setRegisterContent(Register $register): void
    {
        $name = $register->getName();
        if ($name == "\$zero") {
            return;
        }
        $this->i_registers[self::$i_assoziate_register_name[$name]] = $register;
    }

    /**
     * @return array returns complete register values
     */
    public function getCompleteContent(): array
    {
        $result = array();
        for ($i = 0; $i < count($this->i_registers); $i++) {
            if ($this->i_registers[$i]->getValue() == 0) {
                continue;
            }
            $result[$i] = array(
                "reg" => $i,
                "val" => $this->i_registers[$i]->getValue()
            );
        }
        return $result;
    }

    /**
     * reads locked state from register
     * @param string $name name of a register
     * @return bool locked state of register
     */
    public function getLockedState(string $name): bool
    {
        if ($name == "\$zero") {
            return false;
        }
        return $this->i_registers[self::$i_assoziate_register_name[$name]]->getLockState();
    }
}