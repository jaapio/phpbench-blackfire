<?php

declare(strict_types=1);

namespace Jaapio\Blackfire;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class Installer
{

    public static function dumpAutoload()
    {
        $autoloadFile = fopen(__DIR__ . '/../src/autoload.php', 'wb+');

        $fileHead = <<<FILEHEAD
        /**
         * This class is generated by jaapio/phpbench-blackfire, specifically by
         * bin/dumpautoload.php
         *
         * This file is overwritten at every run of `composer install` or `composer update`.
         */

FILEHEAD;


        fwrite($autoloadFile, $fileHead);

        self::dumpautoloadFolder($autoloadFile, __DIR__ . '/../vendor/blackfire/php-sdk/src/Blackfire');
        self::dumpautoloadFolder($autoloadFile, __DIR__ . '/../vendor/composer/ca-bundle/src');

    }


    private static function dumpautoloadFolder($autoloadFile, $path)
    {
        $directoryIterator = new RecursiveDirectoryIterator(
            $path
        );

        $iterator = new RecursiveIteratorIterator($directoryIterator);

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                fwrite($autoloadFile, "require_once '" . realpath($file->getPathname()) . "';" . PHP_EOL);
            }
        }
    }
}
