<?php
/**
 * API of simulator
 */
ini_set('max_execution_time', '300');
define('NICE_PROJECT', true);
require_once 'bin/inc.php';

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    ApiHelper::permissionDenied();
}
if (key_exists("CONTENT_TYPE", $_SERVER) === false) {
    ApiHelper::permissionDenied();
}
if ($_SERVER["CONTENT_TYPE"] !== "application/json") {
    ApiHelper::permissionDenied();
}
$input = file_get_contents('php://input');
$json = ApiHelper::decode_json($input);
if ($json === null) {
    $json = array();
    foreach (array_keys($_POST) as $key) {
        $json[$key] = filter_input(INPUT_POST, $key);
    }
}

if (isset($_SESSION["username"]) == false) {
    if ($json['type'] != ("cma" || "ibd" || "cue")) {
        ApiHelper::permissionDenied();
    }
}
$result = ApiHelper::generateError();
switch ($json['type']) {
    case "rtb": //for testing purposes, returns send request
        $result = $json;
        break;
    case "run":
        $result = ApiHelper::runSrcCode($json);
        break;
    default:
        $result = ApiHelper::generateError();
        break;
}
ApiHelper::generateJson($result);