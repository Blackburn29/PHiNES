#!/usr/bin/env php
<?php

require __DIR__.'/../src/autoload.php';

use Symfony\Component\Console\Application;
use PMG\MBA\Commands\UpdateBidsCommand;
use PMG\MBA\Commands\GetBidModifiersCommand;

$application = new Application();
$application->add(new UpdateBidsCommand());
$application->add(new GetBidModifiersCommand());
$application->run();
