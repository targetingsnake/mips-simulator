<?php

/**
 * result of a part pipeline reset
 */
class ResetPipelineResult
{
    /**
     * @var array array with removed command
     */
    private array $i_commands;

    /**
     * initializes variables
     */
    public function __construct()
    {
        $this->i_commands = array();
    }

    /**
     * number of removed commands
     * @return int amount of removed commands
     */
    public function getLengthDeleted(): int
    {
        return count($this->i_commands);
    }

    /**
     * adds a command to array
     * @param Command $cmd command which will be added
     */
    public function addCommandDeleted(Command $cmd): void
    {
        $this->i_commands[] = $cmd;
    }

    /**
     * returns certain command from array
     * @param int $pos position of command in array
     * @return Command command from array
     */
    public function getCommandDeleted(int $pos): Command
    {
        return $this->i_commands[$pos];
    }
}