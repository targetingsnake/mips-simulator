<?php

/**
 * static class to check input
 */
class InputChecker
{
    /**
     * @param string $input user defined programm
     * @return string user defines program without blank lines or comment lines
     */
    public static function removeEmptyLinesAndCommentLines(string $input) : string {
        $result = "";
        $programmLines = array();
        $lines = explode(PHP_EOL, $input);
        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (strlen(utf8_decode($line)) == 0) {
                continue;
            }
            $programmLines[] = $line;
        }
        for ($j = 0; $j < count($programmLines); $j++) {
            $result .= $programmLines[$j] . PHP_EOL;
        }
        return $result;
    }
}