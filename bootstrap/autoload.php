<?php

if (!function_exists('base_path')) {
    function dd($params)
    {
        if (!is_array($params) && !is_object($params)) {
            var_dump($params);
        }
        print_r($params);
        die();
    }
}

if (!function_exists('base_path')) {
    function base_path($path = '')
    {
        if (strpos($path, '/') !== 0) {
            $path = '/' . $path;
        }
        return realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../') . $path;
    }
}

if (is_file($autoload = __DIR__ . '/../vendor/autoload.php')) {
    require_once $autoload;
} elseif (is_file($autoload = __DIR__ . '/../../../autoload.php')) {
    require_once $autoload;
} else {
    echo "File autoload.php is missing, please update the composer.\n";
    exit(2);
}
unset($autoload);
