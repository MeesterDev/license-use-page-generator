<?php

namespace MeesterDev\LicenseUsePageGenerator\Util;

abstract class StdErr {
    private static $stream = null;

    public static function print(string $output): void {
        fwrite(static::getStream(), $output . PHP_EOL);
    }

    private static function getStream() {
        return static::$stream ?? (static::$stream = defined('STDERR') ? STDERR : (defined('STDOUT') ? STDOUT : fopen('php://output', 'w')));
    }
}