<?php

$loader = require __DIR__.'/../vendor/autoload.php';

$env = new \Dotenv\Dotenv(__DIR__.'/../');
$env->load();

unset($env);

return $loader;
