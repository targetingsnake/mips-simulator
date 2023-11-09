<?php
/**
 * This file includes all necessary files, used in files above this directory level
 * @author targetingsnake
 */

if (!defined('NICE_PROJECT')) {
    die('Permission denied.');
}

require_once 'bin/simulator/ControlComponents/StandardComponents/inc.php';
require_once 'bin/simulator/ControlComponents/SuperscalarModules/inc.php';
require_once 'bin/simulator/ControlComponents/ControllerMain.php';
require_once 'bin/simulator/ControlComponents/ControllerStandardPipeline.php';
require_once 'bin/simulator/ControlComponents/SuperscalarInOrderModules/inc.php';
require_once 'bin/simulator/ControlComponents/SuperscalarOutOfOrderModules/inc.php';
require_once 'bin/simulator/ControlComponents/ControllerSuperscalarStub.php';
require_once 'bin/simulator/ControlComponents/ControllerSuperSkalarIO.php';
require_once 'bin/simulator/ControlComponents/ControllerSuperSkalarOoO.php';
