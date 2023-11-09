<?php

/**
 * represents a command
 */
class Command
{
    /**
     * @var int id of command, place in programm memory
     */
    private int $i_id;

    /**
     * @var string name of command
     */
    private string $i_command;

    /**
     * @var string complete text of command
     */
    private string $i_text;

    /**
     * @var int|string first argument of command
     */
    private $i_argument1;

    /**
     * @var int|string second argument of command
     */
    private $i_argument2;

    /**
     * @var int|string third argument of command
     */
    private $i_argument3;

    /**
     * @var array documents where command was in each cycle
     */
    private array $i_historie;

    /**
     * @var int
     */
    private int $i_argument_count;

    /**
     * @var int defines unique id to identify command if repeated multiple times
     */
    private int $i_process_id;

    /**
     * @var int sets certain execution time
     */
    private int $i_processTime;

    /**
     * @var bool determines if command is any kind of jump
     */
    private bool $jump;

    /**
     * @var int defines Type of command
     * 0 - Type I
     * 1 - Type J
     * 2 - Type R
     * 3 - Memory commands
     * 4 - Pseudo commands
     * 5 - JumpTypes 2 arguments
     * 6 - JumpTypes 3 arguments
     */
    private int $i_type;

    /**
     * @var bool Prediction when command is fetched
     */
    private bool $i_predict;

    /**
     * @param string $command command in textform
     * @param int $id id of command
     */
    public function __construct(string $command, int $id)
    {
        $this->i_historie = array();
        if (strpos($command, "#") !== false) {
            $command = explode("#", $command)[0];
            $command = trim($command);
        }
        $this->i_text = $command;
        $command = str_replace(",", " ", $command);
        $command = str_replace("  ", " ", $command);
        $this->i_argument_count = 0;
        $this->jump = false;
        $this->i_id = $id;
        $inputParameters = explode(" ", $command);
        $this->i_process_id = -1;
        $this->i_type = -1;
        $this->i_command = "";
        $this->i_processTime = 1;
        $this->i_predict = false;
        switch (count($inputParameters)) {
            case 2:
                $this->i_command = trim($inputParameters[0]);
                $this->i_argument1 = trim($inputParameters[1]);
                $this->i_argument_count = 1;
                break;
            case 3:
                $this->i_command = trim($inputParameters[0]);
                $this->i_argument1 = trim($inputParameters[1]);
                $this->i_argument2 = trim($inputParameters[2]);
                $this->i_argument_count = 3;
                break;
            case 4:
                $this->i_command = trim($inputParameters[0]);
                $this->i_argument1 = trim($inputParameters[1]);
                $this->i_argument2 = trim($inputParameters[2]);
                $this->i_argument3 = trim($inputParameters[3]);
                $this->i_argument_count = 3;
                break;
        }
        $this->i_getType();
        if ($this->i_type == 4) {
            $this->i_substituePsuedoCommand();
            $this->i_getType();
        }
    }

    /**
     * Get keyword of Command
     * @return string returns keyword of command
     */
    public function getCommand(): string
    {
        return $this->i_command;
    }

    /**
     * returns stats of command
     * @return array structured result
     */
    public function getStats(): array
    {
        return array(
            "pid" => $this->i_process_id,
            "id" => $this->i_id,
            "history" => $this->i_historie,
            "command" => $this->i_text,
            "jump" => $this->jump,
            "pred" => $this->i_predict
        );
    }

    /**
     * returns command text for debug reasons
     * @return string text of command
     */
    public function getText(): string
    {
        return $this->i_text;
    }

    /**
     * Get Arguments for command
     * @return array|string[]|int[] returns array of all arguments
     */
    public function getArguments(): array
    {
        switch ($this->i_argument_count) {
            case 1:
                return array(1 => $this->i_argument1);
            case 2:
                return array(
                    1 => $this->i_argument1,
                    2 => $this->i_argument2,
                );
            case 3:
                return array(
                    1 => $this->i_argument1,
                    2 => $this->i_argument2,
                    3 => $this->i_argument3
                );
            default:
                return array();
        }
    }

    /**
     * logs position in terms of pipeline
     * @param string $pos logs current postion of command in pipeline
     * @param string $takt current takt of execution of Programm
     */
    public function log_history(string $pos, int $takt): void
    {
        $this->i_historie[$takt] = $pos;
    }

    /**
     * returns ID of command
     * @return int Id of Command
     */
    public function getId(): int
    {
        return $this->i_id;
    }

    /**
     * Gets unique identifier of command when executed
     * @return int unique identifier of command
     */
    public function getProcessId(): int
    {
        return $this->i_process_id;
    }

    /**
     * Sets process id of command
     * @param int $Id Identifier of current running
     */
    public function setProcessId(int $Id): void
    {
        $this->i_process_id = $Id;
    }

    /**
     * Returns type of command
     * @return int type of command as integer
     */
    public function getCommandType(): int
    {
        return $this->i_type;
    }

    /**
     * substitues the pseudo-command
     */
    private function i_substituePsuedoCommand()
    {
        switch ($this->i_command) {
            case "b":
                $this->i_command = "bgez";
                $this->i_argument2 = $this->i_argument1;
                $this->i_argument1 = "\$zero";
                break;
            case "beqz":
                $this->i_command = "beq";
                $this->i_argument3 = $this->i_argument2;
                $this->i_argument2 = $this->i_argument1;
                $this->i_argument1 = "\$zero";
                break;
            case "bnez":
                $this->i_command = "bne";
                $this->i_argument3 = $this->i_argument2;
                $this->i_argument2 = $this->i_argument1;
                $this->i_argument1 = "\$zero";
                break;
        }
    }

    /**
     * gets and decreases Remaining Execution time of command;
     * @return int remaining processtime of command
     */
    public function getExecutionTimeRemaining(): int
    {
        return $this->i_processTime;
    }

    /**
     * decreases remaining execution time
     */
    public function decreaseExecutionTime(): void
    {
        $this->i_processTime = $this->i_processTime - 1;
    }

    /**
     * figures out Type of command
     * 0 - Type I
     * 1 - Type J
     * 2 - Type R
     * 3 - Memory commands
     * 4 - Pseudo commands
     * 5 - JumpTypes 2 arguments
     * 6 - JumpTypes 3 arguments
     */
    private function i_getType(): void
    {
        $result = -1;
        switch ($this->i_command) {
            //Lade und Speicherbefehle
            case "sw":
                $this->i_processTime = 1;
                $result = 3;
                break;
            case "lw":
                $this->i_processTime = 3;
                $result = 3;
                break;
            // Pseudo commands
            case "b":
            case "beqz":
            case "bnez":
                $this->i_processTime = 1;
                $result = 4;
                break;
            // Commands Type I
            case "addi":
            case "addiu":
            case "andi":
            case "ori":
            case "xori":
            case "sll":
            case "srl":
            case "sra":
            case "slti":
            case "sltui":
                $this->i_processTime = 1;
                $result = 0;
                break;
            // Commands Type R
            case "add":
            case "addu":
            case "sub":
            case "subu":
            case "and":
            case "nor":
            case "or":
            case "xor":
            case "sgt":
            case "sgtu":
            case "slt":
            case "sltu":
            case "sllv":
            case "srlv":
            case "srav":
                $this->i_processTime = 1;
                $result = 2;
                break;
            //Jump commands
            case "j":
                $this->i_processTime = 1;
                $result = 1;
                $this->jump = true;
                break;
            // Jump with 2 arguments
            case "bgez":
            case "bgtz":
            case "blez":
            case "bltz":
                $this->i_processTime = 1;
                $result = 5;
                $this->jump = true;
                break;
            // Jump with 3 arguments
            case "beq":
            case "bne":
                $this->i_processTime = 1;
                $result = 6;
                $this->jump = true;
                break;
            default:
                $result = -1;
                break;
        }
        $this->i_type = $result;
    }

    /**
     * sets prediction for this command
     * @param bool $prediction state of prediction if jump is taken
     * @return void
     */
    public function setPrediction(bool $prediction): void
    {
        $this->i_predict = $prediction;
    }

    /**
     * requests saved prediction
     * @return bool state if jump was predicted as taken
     */
    public function getPrediction(): bool
    {
        return $this->i_predict;
    }
}