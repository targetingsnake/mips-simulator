<?php

/**
 * implements all necessary things for jump predition with 1 bit
 */
class JumpPrediction1
{
    /**
     * @var array branch history table
     */
    protected array $i_branch_history_table;

    /**
     * initialises internal variables
     */
    public function __construct()
    {
        $this->i_branch_history_table = array();
        for ($i = 0; $i < 64; $i++){
            $this->i_branch_history_table[$i] = 0;
        }
    }

    /**
     * updates BHT with result of conditional jump
     * @param int $index index of command
     * @param bool $predict result of jump-command
     * @return void
     */
    public function addPrediction(int $index, bool $predict) : void {
        $index_part = $index % 64;
        $this->i_branch_history_table[$index_part] = $predict ? 1 : 0;
    }

    /**
     * get a Prediction from BHT
     * @param int $index index of command
     * @return bool Prediction
     */
    public function getPrediction(int $index): bool {
        $index_part = $index % 64;
        return ($this->i_branch_history_table[$index_part] == 1);
    }

    /**
     * returns stats of jump prediction and is used by logging
     * @return array array with entries of jump prediction
     */
    public function getStats() : array {
        $result = array();
        for ($i = 0; $i < 64; $i++){
            if ($this->i_branch_history_table[$i] > 0) {
                $result[$i] = $this->i_branch_history_table[$i];
            }
        }
        return $result;
    }

}