<?php
/**
 * This file includes all necessary files, used in files above this directory level
 * @author targetingsnake
 */
if (!defined('NICE_PROJECT')) {
    die('Permission denied.');
}


require_once 'bin/simulator/Register.php';
require_once 'bin/simulator/InputChecker.php';
require_once 'bin/simulator/MemoryCell.php';
require_once 'bin/simulator/Memory.php';
require_once 'bin/simulator/Command.php';
require_once 'bin/simulator/pipelineComponents/inc.php';
require_once 'bin/simulator/ProgramMemory.php';
require_once "bin/simulator/Registers.php";
require_once "bin/simulator/ControlComponents/inc.php";