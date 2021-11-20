#!/usr/bin/env php
<?php
require __DIR__.'/vendor/autoload.php';

use App\CronParser;

if (!isset($argv[1])) {
    echo 'Please provide cron string';
    return;
}


$cronString = $argv[1];

$parser = new CronParser($argv[1]);

$parser->parseCronString();
