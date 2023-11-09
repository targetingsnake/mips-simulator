<?php

/**
 * this class is a dummy for jump prediction
 */
class JumpPrediction0 extends JumpPrediction1
{
    /**
     * constructor of dummy, calls instructor of JumpPrediction1
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * dummy for add prediction with parameters to keep interface
     * @param int $index index of command
     * @param bool $predict result
     */
    public function addPrediction(int $index, bool $predict) : void {
        return;
    }

    /**
     * dummy for getPrediction and returns always false
     * @param int $index index of command
     * @return bool Prediction
     */
    public function getPrediction(int $index): bool {
        return false;
    }
}