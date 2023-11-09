<?php
/**
 * sets required settings
 */

if (!defined('NICE_PROJECT')) {
    die('Permission denied.');
}

if (config::$DEBUG === true) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}