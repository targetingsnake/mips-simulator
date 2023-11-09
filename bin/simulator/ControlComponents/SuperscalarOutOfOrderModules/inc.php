<?php
/**
 * This file includes all necessary files, used in files above this directory level
 * @author targetingsnake
 */
if (!defined('NICE_PROJECT')) {
    die('Permission denied.');
}

require_once 'bin/simulator/ControlComponents/SuperscalarOutOfOrderModules/PipelineSuperskalarExecOoO.php';
require_once 'bin/simulator/ControlComponents/SuperscalarOutOfOrderModules/ExecutePipelineContainerOoO.php';
require_once 'bin/simulator/ControlComponents/SuperscalarOutOfOrderModules/ShadowRegisters.php';
require_once 'bin/simulator/ControlComponents/SuperscalarOutOfOrderModules/StateObjectOoO.php';
require_once 'bin/simulator/ControlComponents/SuperscalarOutOfOrderModules/BufferReOrderWriteBack.php';
require_once 'bin/simulator/ControlComponents/SuperscalarOutOfOrderModules/ResetPipelineResult.php';