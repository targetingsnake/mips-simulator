<?php

if (!defined('NICE_PROJECT')) {
    die('Permission denied.');
}

/**
 * configuration class
 */
class config
{
    /**
     * @var bool if true debug is enables
     */
    public static $DEBUG = false;

    /**
     * @var int sets debug level
     */
    public static $DEBUG_LEVEL = 0;

    /**
     * @var int sets length of random strings
     */
    public static $RANDOM_STRING_LENGTH = 170;

    /**
     * @var string sets main caption of website
     */
    public static $MAIN_CAPTION = "MIPS-Sim";

    /**
     * @var string sets tagline for website
     */
    public static $TAGLINE_CAPTION = "MIPS Simulator";

    /**
     * @var bool if true beta mode is enabled
     */
    public static $BETA = false;

    /**
     * @var bool if true maintenance mode is enables
     */
    public static $MAINTENANCE = false;
}
