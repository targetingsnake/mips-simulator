<?php

/**
 * container to hold part pipelines
 */
class ExecutePipelineContainerOoO
{
    /**
     * @var array array with part pipelines
     */
    private array $execPipelines;

    /**
     * Initializes class
     * @param int $number number of part pipelines
     * @param string $programmCode simulated program code
     * @param int $jmpPred defines which jump prediction is used
     */
    public function __construct(int $number, string $programmCode, int $jmpPred)
    {
        for ($i = 0; $i < $number; $i++) {
            $this->execPipelines[] = new PipelineSuperskalarExecOoO($programmCode, $jmpPred);
        }
    }

    /**
     * returns certain part pipeline
     * @param int $pos position of part pipeline in container
     * @return PipelineSuperskalarExecOoO chosen part pipeline
     */
    public function getPipeline(int $pos): PipelineSuperskalarExecOoO
    {
        return $this->execPipelines[$pos];
    }

    /**
     * inserts part pipeline back to array
     * @param int $pos position where part pipeline should be inserted
     * @param PipelineSuperskalarExecOoO $pipeline part pipeline to insert
     */
    public function InsertPipeline(int $pos, PipelineSuperskalarExecOoO $pipeline): void
    {
        $this->execPipelines[$pos] = $pipeline;
    }
}