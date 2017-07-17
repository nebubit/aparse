<?php

require_once('vendor/autoload.php');

$fishPool = new \AParse\FishPool();
$fishPool->currentPath = getcwd();

$variables = [
    'db' => new \AParse\Igniter($fishPool),
];

$shell = new \Psy\Shell(null);
$shell->setScopeVariables($variables);
$shell->add(new \AParse\Commands\UseFileCommand());
$shell->run();
