<?php

namespace AParse;

interface StringInterface
{
    /**
     * parse a line string of Apache access log
     *
     * @param string $lineString
     * @return array line string to array
     */
    public function parseAccessLogLineToArray($lineString);
}
