<?php

class Execution extends Component
{
    /**
     * no special variables need an initialisation
     */
    public function __construct()
    {
        parent::__construct("EX");
    }

    /**
     * executes type I commands
     * @param Command $cmd command which will be executed
     * @param int $reg Register as input
     * @return Register register which will be written back
     */
    public function ExecuteTypeI(Command $cmd, int $reg): Register
    {
        $result = new Register($cmd->getArguments()[1]);
        $result->setLockState(false);
        $return = null;
        switch ($cmd->getCommand()) {
            case "addi":
            case "addiu":
                $return = $reg + intval($cmd->getArguments()[3]);
                break;
            case 'andi':
                $return = $reg & intval($cmd->getArguments()[3]);
                break;
            case 'ori':
                $return = $reg | intval($cmd->getArguments()[3]);
                break;
            case 'xori':
                $return = $reg ^ intval($cmd->getArguments()[3]);
                break;
            case 'sll':
                if (!(intval($cmd->getArguments()[3]) < 0)) {
                    $return = $reg << intval($cmd->getArguments()[3]);
                } else {
                    $return = 0;
                }
                break;
            case 'srl':
                if (!(intval($cmd->getArguments()[3]) < 0)) {
                    if ($reg < 0 && $cmd->getArguments()[3] > 0) {
                        $reg = Helper::convertSignedToUnsigned($reg);
                    }
                    $return = $reg >> intval($cmd->getArguments()[3]);
                } else {
                    $return = 0;
                }
                break;
            case 'sra':
                $powValue = pow(2, intval($cmd->getArguments()[3]));
                if (!is_infinite($powValue) && $powValue > 0) {
                    $return = intval($reg / $powValue);
                } else {
                    $return = 0;
                }
                break;
            case 'slti':
                $return = $reg < intval($cmd->getArguments()[3]) ? 1 : 0;
                break;
            case 'sltui':
                $return = Helper::convertSignedToUnsigned($reg) < Helper::convertSignedToUnsigned(intval($cmd->getArguments()[3])) ? 1 : 0;
                break;
        }
        $return = $this->limit32Bit($return); // 2^31 to negligate
        $result->setValue($return);
        return $result;
    }

    /**
     * Executes a type J command
     * @param Command $cmd command which will be executed
     * @return string label to return
     */
    public function ExecuteTypeJ(Command $cmd): string
    {
        return $cmd->getArguments()[1];
    }

    /**
     * Executes a type R command
     * @param Command $cmd command which will be executed
     * @param Register $reg1 register which is needed for calculation
     * @param Register $reg2 register which is needed for calculation
     * @return Register register which contains result of calculation
     */
    public function ExecuteTypeR(Command $cmd, int $reg1, int $reg2): Register
    {
        $result = new Register($cmd->getArguments()[1]);
        $result->setLockState(false);
        $return = null;
        switch ($cmd->getCommand()) {
            case "add":
            case "addu":
                $return = $reg1 + $reg2;
                break;
            case "subu":
            case "sub":
                $return = $reg1 - $reg2;
                break;
            case 'and':
                $return = $reg1 & $reg2;
                break;
            case 'or':
                $return = $reg1 | $reg2;
                break;
            case 'xor':
                $return = $reg1 ^ $reg2;
                break;
            case 'nor':
                $return = ~($reg1 | $reg2);
                break;
            case "sgt":
                $return = $reg1 > $reg2 ? 1 : 0;
                break;
            case "sgtu":
                $return = Helper::convertSignedToUnsigned($reg1) > Helper::convertSignedToUnsigned($reg2) ? 1 : 0;
                break;
            case "slt":
                $return = $reg1 < $reg2 ? 1 : 0;
                break;
            case "sltu":
                $return = Helper::convertSignedToUnsigned($reg1) < Helper::convertSignedToUnsigned($reg2) ? 1 : 0;
                break;
            case "sllv":
                if (!($reg2 < 0)) {
                    $return = $reg1 << $reg2;
                } else {
                    $return = 0;
                }
                break;
            case "srlv":
                if (!($reg2 < 0)) {
                    if ($reg1 < 0 && $reg2 > 0) {
                        $reg1 = Helper::convertSignedToUnsigned($reg1);
                    }
                    $return = $reg1 >> $reg2;
                } else {
                    $return = 0;
                }
                break;
            case "srav":
                $powValue = pow(2, $reg2);
                if (!is_infinite($powValue) && $powValue > 0) {
                    $return = intval(($reg1 / $powValue));
                } else {
                    $return = 0;
                }
                break;
        }
        $return = $this->limit32Bit($return); // 2^31 to negligate
        $result->setValue($return);
        return $result;
    }

    /**
     * sorts out jump with 2 or more parameters
     * @param Command $cmd command which is executed
     * @param int $reg1 register value which is needed
     * @return array structured result
     */
    public function ExecuteJump2Params(Command $cmd, int $reg1): array
    {
        $result = array(
            "jump" => false,
            "label" => ""
        );
        switch ($cmd->getCommand()) {
            case "bgez":
                if ($reg1 >= 0) {
                    $result = array(
                        "jump" => true,
                        "label" => $cmd->getArguments()[2]
                    );
                }
                break;
            case "bgtz":
                if ($reg1 > 0) {
                    $result = array(
                        "jump" => true,
                        "label" => $cmd->getArguments()[2]
                    );
                }
                break;
            case "blez":
                if ($reg1 <= 0) {
                    $result = array(
                        "jump" => true,
                        "label" => $cmd->getArguments()[2]
                    );
                }
                break;
            case "bltz":
                if ($reg1 < 0) {
                    $result = array(
                        "jump" => true,
                        "label" => $cmd->getArguments()[2]
                    );
                }
                break;
        }
        return $result;
    }

    /**
     * executes jump command with 3 parameters
     * @param Command $cmd command which will be executed
     * @param int $reg1 register 1 value
     * @param int $reg2 register 2 value
     * @return array structured result
     */
    public function ExecuteJump3Params(Command $cmd, int $reg1, int $reg2): array
    {
        $result = array(
            "jump" => false,
            "label" => ""
        );
        switch ($cmd->getCommand()) {
            case "beq":
                if ($reg1 == $reg2) {
                    $result = array(
                        "jump" => true,
                        "label" => $cmd->getArguments()[3]
                    );
                }
                break;
            case "bne":
                if ($reg1 != $reg2) {
                    $result = array(
                        "jump" => true,
                        "label" => $cmd->getArguments()[3]
                    );
                }
                break;
        }
        return $result;
    }

    /**
     * simulates overflow of register
     * @param int $value input value
     * @return int overflowed value
     */
    private function limit32Bit(int $value): int
    {
        if ($value >= 2147483647) {
            $value = $value % 2147483648;
            $value = -2147483648 + $value;
        }
        if ($value < -2147483648) {
            $value = $value % 2147483649;
            $value = 2147483647 - $value;
        }
        return $value;
    }
}