<?php
/**
 * This file includes all necessary files, so that this website work properly
 *
 * @author targetingsnake
 */
if (!defined('NICE_PROJECT')) {
    die('Permission denied.');
}
header('Content-Type: text/html; charset=utf-8');
require_once 'bin/config.php';
require_once 'bin/Helper.php';
require_once 'bin/settings.php';
require_once 'bin/HtmlGenerator.php';
require_once 'bin/ApiHelper.php';
require_once 'bin/simulator/inc.php';
