<?php

/**
 * static class which contains helping functions
 */
class Helper
{
    /**
     * dumpes data, if debug mode is enabled and filters debug output based on config::$DEBUG_LEVEL
     * @param mixed $data data that should be printed for debug purposes
     * @param int $level If the value is greater or equal than config::$DEBUG_LEVEL the variable to debug will be printed out
     * @param bool $dark if true dump has dark background
     */
    public static function dump($data, $level = -1, $dark = false)
    {
        if ($level != -1) {
            if ($level > config::$DEBUG_LEVEL) {
                return;
            }
        }
        if (config::$DEBUG) {
            ?>
            <code style='display: block;white-space: pre-wrap;'>
                <?php
                var_dump($data);
                ?>
            </code>
            <br/>
            <br/>
            <?php
            if ($dark) {
                ?>
                <div class="bg-dark">
                    <code style='display: block;white-space: pre-wrap;'>
                        <?php
                        var_dump($data);
                        ?>
                    </code>
                    <br/>
                    <br/>
                </div>
                <?php
            }
        }
    }

    /**
     * convert integers from signed to unsigned
     * @param int $value signed value
     * @return int unsigned value
     */
    public static function convertSignedToUnsigned(int $value): int {
        if ($value < 0) {
            return 1 + 4294967295 + $value;
        }
        return $value;
    }
}