<?php

namespace AParse;

function dd($params)
{
    if (!is_array($params) && !is_object($params)) {
        var_dump($params);
    }
    print_r($params);
    die();
}

function line($string)
{
    return $string . "\n";
}

/**
 * Tell the program which file to use.
 *
 * @param $fileName
 * @return string
 */
function useFile($fileName)
{
    $fileName = ltrim($fileName, '/');
    $fileName = ltrim($fileName, '\\');
    global $fishPool;
    $fishPool->currentFilePath = $fishPool->currentPath . DIRECTORY_SEPARATOR . $fileName;
    return ('Using file ' . $fileName);
}
