#!/usr/bin/env php
<?php
use Hatchup\Command;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/app/constants.php';

// the command dir will be scanned for command line commands
$commandDir = __DIR__ . '/src/Hackup/Command';

// execute the command
Command::execute($commandDir);
