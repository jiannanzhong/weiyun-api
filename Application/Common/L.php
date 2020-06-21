<?php

namespace WeiyunBackend\Common;

use Monolog\Logger;

class L
{
    /**
     * @var Logger
     */
    private static $logger = null;

    public static function getLogger()
    {
        if (self::$logger === null) {
            global $app;
            self::$logger = $app->getContainer()->get('logger');

        }
        return self::$logger;
    }
}