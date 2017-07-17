<?php

namespace Tests;

use AParse\LineString;
use PHPUnit\Framework\TestCase;

class LineStringTest extends TestCase
{
    public function testParseCsvLineToArray()
    {
        $lineString = 'test,data';
        $data = (new LineString())->parseCsvLineToArray($lineString);
        self::assertTrue(explode(',', $lineString) == $data);
    }
}
