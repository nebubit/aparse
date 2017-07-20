<?php

namespace Tests;

use AParse\LineString;
use AParse\ProcessQueryInterface;

/**
 * @coversDefaultClass \AParse\LineString
 */
class LineStringTest extends BaseTest
{
    public $apacheAccessLogLine = '192.168.1.6 192.168.1.124 - - [11/Jul/2017:19:09:40 -0700] "GET / HTTP/1.1" 200 2879 "-" "check_http/v2.1.1 (plugins 2.1.1)"';

    public $lineString;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->lineString = new LineString();
    }

    /**
     * @covers ::parseLineToArray
     */
    public function testParseLineToArrayWithWrongType()
    {
        $data = $this->lineString->parseLineToArray('not matched line', 'unknownType');
        self::assertEquals(3, count($data));
    }

    /**
     * @covers ::parseLineToArray
     */
    public function testParseLineToArrayWithApacheAccessLog()
    {
        $data = $this->lineString->parseLineToArray($this->apacheAccessLogLine, 'access');
        self::assertEquals(7, count($data));
    }

    /**
     * @covers ::parseLineToArray
     */
    public function testParseLineToArrayWithCsvLog()
    {
        $data = $this->lineString->parseLineToArray('a,b,c', 'csv');
        self::assertEquals(3, count($data));
    }

    /**
     * @covers ::parseCsvLineToArray
     */
    public function testParseCsvLineToArray()
    {
        $lineString = 'test,data';
        $data = $this->lineString->parseCsvLineToArray($lineString);
        self::assertTrue(explode(',', $lineString) == $data);
    }

    /**
     * @covers ::parseAccessLogLineToArray
     */
    public function testParseAccessLogLineToArray()
    {
        $data = $this->lineString->parseAccessLogLineToArray($this->apacheAccessLogLine);

        // Test the parse rule
        self::assertEquals(7, count($data));

        // Test the key
        self::assertEquals('200', $data[ProcessQueryInterface::KEY_PREFIX . '3']);
    }

    /**
     * @covers ::cutAccessLogToPieces
     */
    public function testCutAccessLogToPieces()
    {
        $string = explode(' - - ', $this->apacheAccessLogLine);
        $string = $string[1];
        $data = $this->lineString->cutAccessLogToPieces($string);
        self::assertEquals(6, count($data));
        self::assertEquals('200', $data[2]);
    }

    /**
     * @covers ::parseAccessLogLineToArray
     */
    public function testParseAccessLogLineToArrayWithoutMatchedLine()
    {
        $data = $this->lineString->parseAccessLogLineToArray('not matched line');
        self::assertEmpty($data);
    }

    /**
     * @covers ::parseLineByDefaultRule
     */
    public function testParseLineByDefaultRule()
    {
        $data = $this->lineString->parseLineByDefaultRule('not matched line');
        self::assertEquals(3, count($data));
    }
}
