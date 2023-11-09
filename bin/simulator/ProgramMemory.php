<?php

/**
 * represents a memory cell
 */
class ProgramMemory
{
    /**
     * @var array Array of all commands ordered by ID
     */
    private array $i_commands;

    /**
     * @var array assertative array from label to command id
     */
    private array $i_labels;

    /**
     * Inits Programm memory
     * @param string $programm Code of Programm without whitespaces, comments
     */
    public function __construct(string $programm)
    {
        $this->i_commands = array();
        $lines = explode(PHP_EOL, $programm);
        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if(strpos($line, '#') === 0){
                $parts = explode("#", $line);
                $line = $parts[0];
                $line = trim($line);
            }
            if(strpos($line, ":") !== false){
                $parts = explode(":", $line);
                $this->i_labels[trim($parts[0])] = $i;
                $line = trim($parts[1]);
            }
            $line = trim($line);
            $line = preg_replace('/\s+/', ' ', $line);
            if ($line == "") {
                continue;
            }
            $this->i_commands[$i] = new Command($line, $i);
        }
    }

    /**
     * Returns complete memory
     * @return array array with all commands
     */
    public function getProgrammMemory() : array {
        return $this->i_commands;
    }

    /**
     * Checks for id of command from Label
     * @param string $label name of label
     * @return int returns id of command
     */
    public function getLabel(string $label) : int {
        return $this->i_labels[$label];
    }

    /**
     * Return specified command
     * @param int $id id of command
     * @return Command return command
     */
    public function getCommand(int $id) : Command {
        return $this->i_commands[$id];
    }

    /**
     * Get Count of commands
     * @return int count of commands
     */
    public function getProgrammLength() : int {
        return count($this->i_commands);
    }
}