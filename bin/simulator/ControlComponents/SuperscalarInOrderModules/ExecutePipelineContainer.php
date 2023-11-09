<?php

/**
 * container to hold multiple part pipelines
 */
class ExecutePipelineContainer
{
    /**
     * @var array array with part pipelines
     */
    private array $execPipelines;

    /**
     * initializes variables prepare part pipelines
     * @param int $number number of pipelines to be prepared
     * @param string $programmCode code which will be simulated
     * @param int $jmpPred defines which jump prediction is used
     * @param bool $res_fwd defines if result forward is used
     */
    public function __construct(int $number, string $programmCode, int $jmpPred, bool $res_fwd)
    {
        for ($i = 0; $i < $number; $i++) {
            $this->execPipelines[] = new PipelineSuperskalarExec($programmCode, $jmpPred, $res_fwd);
        }
    }

    /**
     * returns a part pipeline to caller
     * @param int $pos position of part pipeline in array
     * @return PipelineSuperskalarExec requested part pipeline
     */
    public function getPipeline(int $pos): PipelineSuperskalarExec
    {
        return $this->execPipelines[$pos];
    }

    /**
     * inserts a part pipeline to given position
     * @param int $pos position where part pipeline will be inserted
     * @param PipelineSuperskalarExec $pipeline pipeline which will be inserted
     */
    public function InsertPipeline(int $pos, PipelineSuperskalarExec $pipeline): void
    {
        $this->execPipelines[$pos] = $pipeline;
    }
}