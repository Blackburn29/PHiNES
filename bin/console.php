#!/usr/bin/env php
<?php

require __DIR__.'/../src/autoload.php';

use Symfony\Component\Console\Application;

use PHiNES\Bundle\Command\RunEmu;

$application = new Application();
$application->add(new RunEmu());
$application->run();
