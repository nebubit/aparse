<?php

namespace AParse;

interface LineStringInterface
{
    /**
     * Parse a line string to array
     *
     * @param string $lineString
     * @param string $type
     * @return array
     */
    public function parseLineToArray($lineString, $type);
}
