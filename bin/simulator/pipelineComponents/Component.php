<?php

/**
 * base class for every component
 */
class Component
{
    /**
     * @var string name of component
     */
    protected string $i_name_log;

    /**
     * initialzes variables
     * @param $name string name of command
     */
    public function __construct(string $name)
    {
        $this->i_name_log = $name;
    }

    /**
     * operand fetch and prepare command
     * @param Command $cmd command which will be executed
     * @param int $takt takt which is currently running
     * @return Command array with ordered result
     */
    public function processCmd(Command $cmd, int $takt) : Command {
        $cmd->log_history($this->i_name_log, $takt);
        return $cmd;
    }
}