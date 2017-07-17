<?php

require_once('vendor/autoload.php');

$fishPool = new \AParse\FishPool();
$fishPool->currentPath = getcwd();

$variables = [
    'db' => new \AParse\Igniter($fishPool),
    'useFile' => function($fileName){ return \AParse\useFile($fileName);},
];

$shell = new \Psy\Shell(null);
$shell->setScopeVariables($variables);
$shell->run();
