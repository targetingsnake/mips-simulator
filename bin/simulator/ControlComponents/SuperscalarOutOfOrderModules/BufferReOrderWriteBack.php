<?php

/**
 * Special Buffer between reorder and write back
 */
class BufferReOrderWriteBack
{
    /**
     * @var array array with commands nad their result
     */
    protected array $i_commands;

    /**
     * @var array array with state if a certain place is used
     */
    protected array $i_used;

    /**
     * @var bool lock state of Buffer
     */
    protected bool $i_lock;

    /**
     * @var int amount of commands which can be in buffer
     */
    protected int $i_length;

    /**
     * Initializes Buffer
     * @param int $amount amount of commands can be kept
     */
    public function __construct(int $amount)
    {
        $this->i_lock = false;
        $this->i_length = $amount;
        for ($i = 0; $i < $amount; $i++){
            $this->i_commands[$i] = new ReorderBufferCommand(new Register("\$zero"), new Command("", 0), false);
            $this->i_used[$i] = false;
        }
    }

    /**
     * inserts command into buffer;
     * @param int $pos
     * @param ReorderBufferCommand $cmd
     */
    public function insertCommand(int $pos, ReorderBufferCommand $cmd)
    {
        $this->i_commands[$pos] = $cmd;
    }

    /**
     * returns certain command
     * @param int $pos position of command which is requested
     * @return ReorderBufferCommand
     */
    public function getCommand(int $pos): ReorderBufferCommand
    {
        return $this->i_commands[$pos];
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
}