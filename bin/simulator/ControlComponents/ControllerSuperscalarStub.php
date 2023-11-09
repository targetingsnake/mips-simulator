<?php

class ControllerSuperscalarStub extends ControllerMain
{
    /**
     * @var StateObject holds state of processor
     */
    protected StateObject $states;

    /**
     * @var Buffer Buffer between instruction fetch and instruction decode
     */
    protected Buffer $Stage_if_id;

    /**
     * @var int defines number of parallel executed command
     */
    protected int $pipelines;

    /**
     * @var int defines which jump prediction is used
     */
    protected int $jumpPrediction;

    /**
     * @var bool defines if result forward is used
     */
    protected bool $result_forward;

    /**
     * initalizes variable and parent class
     * @param string $programmCode simulated program code
     * @param int $jmpPred defines which jump prediction is used
     * @param bool $res_fwd defines if result forward is used
     * @param int $pipelines defines number of parallel pipelines
     */
    public function __construct(string $programmCode, int $jmpPred, bool $res_fwd, int $pipelines)
    {
        parent::__construct($programmCode);
        $this->Stage_if_id = new Buffer($pipelines);
        $this->pipelines = $pipelines;
        $this->jumpPrediction = $jmpPred;
        $this->result_forward = $res_fwd;
        switch ($jmpPred) {
            case 0:
                $this->states = new StateObject(new JumpPrediction0(), $this->i_programmMemory);
                break;
            case 1:
                $this->states = new StateObject(new JumpPrediction1(), $this->i_programmMemory);
                break;
            case 2:
                $this->states = new StateObject(new JumpPrediction2(), $this->i_programmMemory);
                break;
        }
    }

    /**
     * logs memory and register state after current takt
     * @param int $this- >takt takt which was executed
     */
    protected function logMemoryAndRegister(): void
    {
        $this->i_MemoryHistory[$this->states->takt] = $this->states->i_memory->getMemoryComplete();
        $this->i_RegisterHistory[$this->states->takt] = $this->states->i_registers->getCompleteContent();
        $this->i_BranchHistory[$this->states->takt] = $this->states->i_predict->getStats();
    }
}