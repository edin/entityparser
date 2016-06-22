<?php

$vendorDir =  __DIR__ . "/vendor/autoload.php";

if (!is_file($vendorDir)) 
{
    echo "Please update dependencies using 'composer update' and try again.\n";
    echo "\n";
    exit;
}

require $vendorDir;

use Symfony\Component\Console\Application;
use EntityParser\Parser\Command\ParseCommand;

$application = new Application();
$application->add(new ParseCommand());
$application->run();