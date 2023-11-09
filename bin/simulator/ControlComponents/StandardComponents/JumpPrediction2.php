<?php

/**
 * implements all necessary things for jump predition with 2 bits
 */
class JumpPrediction2 extends JumpPrediction1
{

    /**
     * initialises internal variables from parent class
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * updates BHT depending on current state
     * @param int $index index of command
     * @param bool $predict result of jump command
     */
    public function addPrediction(int $index, bool $predict): void
    {
        $index_part = $index % 64;
        switch ($this->i_branch_history_table[$index_part]) {
            case 0:
                if ($predict) {
                    $this->i_branch_history_table[$index_part] = 1;
                }
                break;
            case 1:
            case 2:
                if ($predict) {
                    $this->i_branch_history_table[$index_part] = 3;
                } else {
                    $this->i_branch_history_table[$index_part] = 0;
                }
                break;
            case 3:
                if (!$predict) {
                    $this->i_branch_history_table[$index_part] = 2;
                }
                break;
        }
    }

    /**
     * returns prediction for certain command
     * @param int $index index of command
     * @return bool result of prediction
     */
    public function getPrediction(int $index): bool
    {
        $index_part = $index % 64;
        $result = false;
        switch ($this->i_branch_history_table[$index_part]) {
            case 0:
            case 1:
                $result = false;
                break;
            case 2:
            case 3:
                $result = true;
                break;
        }
        return $result;
    }
}