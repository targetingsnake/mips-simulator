<?php

if (!defined('NICE_PROJECT')) {
    die('Permission denied.');
}

/**
 * Api-Helper class with some basic functions
 */
class ApiHelper
{
    /**
     * generates json output for api
     * @param array $array all data that should be returned
     */
    public static function generateJson($array): void
    {
        echo json_encode($array);
    }

    /**
     * Denies access to page and ends php execution, always sends http-status-code 403 (Forbidden)
     * @param string $string if string is given it will be send before php execution will be stopped
     */
    public static function permissionDenied($string = ""): void
    {
        if ($string !== "") {
            echo $string;
        } else {
            echo "You shall not pass!";
        }
        http_response_code(403);
        die();
    }

    /**
     * decodes a given json string, parameters for json decoding can be set here
     * @param string $string json as string
     * @return array content of json in accessible format (structured array)
     */
    public static function decode_json(string $string): array
    {
        return json_decode($string, true);
    }

    /**
     * genereates error message for all functions
     * @param null|string|array|int $msg error message to send
     * @return array represents static error message
     */
    public static function generateError($msg = null): array
    {
        if ($msg !== null) {
            return array(
                "result" => "Wrong Request!",
                "code" => 1,
                "msg" => $msg
            );
        }
        return array(
            "result" => "Wrong Request!",
            "code" => 1
        );
    }

    /**
     * generates success message for all functions
     * @param null|string|array|int $payload result of executed request
     * @return array represents static success message
     */
    public static function generateSuccess($payload): array
    {
        return array(
            "result" => "ack",
            "code" => 0,
            "data" => $payload
        );
    }

    /**
     * runs given code in Simulator
     * @param array $json structured request
     * @return array structured result
     */
    public static function runSrcCode($json): array
    {
        $sim = null;
        switch (intval($json['pipeline'])){
            case 0:
                $sim = new ControllerMain($json['src']);
                break;
            case 1:
                $sim = new ControllerStandardPipeline($json['src'], intval($json['jumpPred']), $json['res_fwd']);
                break;
            case 2:
                $sim = new ControllerSuperSkalarIO($json['src'], intval($json['jumpPred']), $json['res_fwd'], $json['pipelines']);
                break;
            case 3:
                $sim = new ControllerSuperSkalarOoO($json['src'], intval($json['jumpPred']), $json['pipelines']);
        }
        if ($sim === null) {
            return self::generateError();
        }
        $sim->run();
        if ($sim->checkError()){
            return self::generateError($sim->getRunStats());
        }
        return self::generateSuccess($sim->getRunStats());
    }
}