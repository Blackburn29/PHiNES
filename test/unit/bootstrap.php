<?php 

@ini_set('auto_detect_line_endings', true);

$loader = require __DIR__.'/../../vendor/autoload.php';
$loader->addPsr4('PHiNES\\', __DIR__);
