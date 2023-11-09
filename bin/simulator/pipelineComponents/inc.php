<?php
/**
 * This file includes all necessary files, used in files above this directory level
 * @author targetingsnake
 */
if (!defined('NICE_PROJECT')) {
    die('Permission denied.');
}

require_once "bin/simulator/pipelineComponents/Component.php";
require_once "bin/simulator/pipelineComponents/InstructionFetch.php";
require_once "bin/simulator/pipelineComponents/InstructionDecode.php";
require_once "bin/simulator/pipelineComponents/OperandFetch.php";
require_once "bin/simulator/pipelineComponents/Execution.php";
require_once "bin/simulator/pipelineComponents/WriteBack.php";
require_once "bin/simulator/pipelineComponents/InstructionSchedulerStub.php";
require_once "bin/simulator/pipelineComponents/InstructionScheduler.php";
require_once "bin/simulator/pipelineComponents/InstructionPool.php";
require_once 'bin/simulator/pipelineComponents/ComponentModules/inc.php';
require_once "bin/simulator/pipelineComponents/ReorderBuffer.php";