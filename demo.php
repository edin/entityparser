<?php

include __DIR__ . "/vendor/autoload.php";

use Symfony\Component\Console\Application;
use EntityParser\Parser\Command\ParseCommand;

$application = new Application();
$application->add(new ParseCommand());
$application->run();