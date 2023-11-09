<?php

/**
 * is a buffer to hold multiple commands
 */
class Buffer
{
    /**
     * @var array array with commands
     */
    protected array $i_commands;

    /**
     * @var array array with information if a place in Buffer is used
     */
    protected array $i_used;

    /**
     * @var bool lock state of Buffer
     */
    protected bool $i_lock;

    /**
     * @var int length of Buffer
     */
    protected int $i_length;

    /**
     * Initalises buffer
     * @param int $amount amount of commands which buffer can contain
     */
    public function __construct(int $amount)
    {
        $this->i_lock = false;
        $this->i_length = $amount;
        for ($i = 0; $i < $amount; $i++) {
            $this->i_commands[$i] = new Command("", 0);
            $this->i_used[$i] = false;
        }
    }

    /**
     * returns lock state
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->i_lock;
    }

    /**
     * sets lock state of buffer
     * @param bool $lock lock state
     */
    public function setLocked(bool $lock): void
    {
        $this->i_lock = $lock;
    }

    /**
     * returns state of buffer cell
     * @param int $pos position which is questioned
     * @return bool true if used
     */
    public function isUsed(int $pos): bool
    {
        return $this->i_used[$pos];
    }

    /**
     * sets used state for buffer cell
     * @param int $pos position which is set
     * @param bool $state state of buffer cell
     */
    public function setUsed(int $pos, bool $state): void
    {
        $this->i_used[$pos] = $state;
    }

    /**
     * inserts command into buffer;
     * @param int $pos
     * @param Command $cmd
     */
    public function insertCommand(int $pos, Command $cmd)
    {
        if ($pos < $this->i_length) {
            $this->i_commands[$pos] = $cmd;
        }
    }

    /**
     * returns certain command
     * @param int $pos position of command which is requested
     * @return Command
     */
    public function getCommand(int $pos): Command
    {
        return $this->i_commands[$pos];
    }
}