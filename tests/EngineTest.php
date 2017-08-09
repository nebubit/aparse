<?php

namespace Tests;

use AParse\Engine;
use AParse\EngineInterface;
use AParse\FishPool;
use AParse\LineString;
use AParse\ProcessQuery;

/**
 * @coversDefaultClass \AParse\Engine
 */
class EngineTest extends BaseTest
{
    /**
     * @var EngineInterface
     */
    public $engine;

    public $accessLogPath;

    public function setUp()
    {
        parent::setUp();

        $process = new ProcessQuery();
        $lineString = new LineString();
        $GLOBALS['fishPool'] = new FishPool();
        $this->engine = new Engine($GLOBALS['fishPool'], $process, $lineString);

        $this->accessLogPath = base_path('tests/data/access.log');
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->engine = null;
    }

    private function getReflectionProperty($instance, $propertyName)
    {
        $engine = (new \ReflectionClass($instance))->getProperty($propertyName);
        $engine->setAccessible(true);
        return $engine->getValue($instance);
    }

    /**
     * @covers ::select
     */
    public function testSelect()
    {
        $fields = ['a', 'b'];
        $this->engine->select($fields);
        $data = $this->getReflectionProperty($this->engine, 'selectedFields');
        self::assertTrue($fields == $data);

        $this->engine->select([$fields]);
        $data = $this->getReflectionProperty($this->engine, 'selectedFields');
        self::assertTrue($fields == $data);
    }

    /**
     * @covers ::where
     */
    public function testWhere()
    {
        $conditions = [
            ['c1=' => 'value1'],
            ['c2>=' => 'value2'],
        ];

        $expectedData = [
            [
                'fieldName' => 'c1',
                'fieldValue' => 'value1',
                'operator' => '=',
            ],
            [
                'fieldName' => 'c2',
                'fieldValue' => 'value2',
                'operator' => '>=',
            ],
        ];
        $this->engine->where($conditions);
        $data = $this->getReflectionProperty($this->engine, 'where');
        self::assertTrue($expectedData == $data);
    }

    /**
     * @covers ::count
     */
    public function testCount()
    {
        $this->engine->count([]);
        $data = $this->getReflectionProperty($this->engine, 'selectedFields');

        self::assertEmpty($data);

        $field = 'c1';
        $expectedData = ['count(c)'];
        $this->engine->count($field);
        $data = $this->getReflectionProperty($this->engine, 'selectedFields');

        self::assertTrue($expectedData == $data);
    }

    /**
     * @covers ::group
     */
    public function testGroupBy()
    {
        $this->engine->group([]);
        $data = $this->getReflectionProperty($this->engine, 'selectedFields');
        self::assertEmpty($data);

        $this->engine->group(['c1']);
        $data = $this->getReflectionProperty($this->engine, 'selectedFields');
        $expectedData = ['c1'];
        self::assertEquals($expectedData, $data);
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        \AParse\useFile($this->accessLogPath);
        $this->engine->select(['c3']);
        $this->engine->get([1]);
        $data = $this->engine->getResult();
        self::assertEquals(1, count($data));

        $this->engine->get([1]);
        $data = $this->engine->getResult();
        self::assertEquals(2, count($data));

        // Reset the records
        $process = new ProcessQuery();
        $lineString = new LineString();
        $GLOBALS['fishPool'] = new FishPool();
        $this->engine = new Engine($GLOBALS['fishPool'], $process, $lineString);

        $this->engine->select(['c3']);
        \AParse\useFile($this->accessLogPath);

        $this->engine->get([1, 1]);
        $data = $this->engine->getResult();
        self::assertEquals(1, count($data));
        self::assertEquals(200, $data[0]['c3']);
    }

    /**
     * @covers ::processLine
     */
    public function testProcessLine()
    {
        $fileHandle = fopen($this->accessLogPath, 'r');
        $lineString = fgets($fileHandle);
        fclose($fileHandle);
        $row = (new LineString())->parseLineToArray($lineString, 'access');

        $this->engine->select(['c3']);
        $data = $this->engine->processLine($row);
        self::assertEquals('301', $data['c3']);

        $conditions = [
            ['c3>=' => '302'],
        ];
        $this->engine->where($conditions);
        $data = $this->engine->processLine($row);
        self::assertEmpty($data);

        $field = ['c1'];
        $this->engine->count($field);
        $this->engine->processLine($row);

        $selectedFields = $this->getReflectionProperty($this->engine, 'selectedFields');
        self::assertTrue(in_array('count(c1)', array_values($selectedFields)));
    }

    /**
     * Must test Count with a matched WHERE, or the result may not right.
     *
     * @covers ::processLine
     */
    public function testProcessLineCount()
    {
        $fileHandle = fopen($this->accessLogPath, 'r');
        $lineString = fgets($fileHandle);
        fclose($fileHandle);
        $row = (new LineString())->parseLineToArray($lineString, 'access');

        $field = ['c1'];
        $conditions = [
            ['c3>=' => '301'],
        ];
        $this->engine->where($conditions);
        $this->engine->count($field);
        $this->engine->processLine($row);

        $selectedFields = $this->getReflectionProperty($this->engine, 'selectedFields');
        self::assertTrue(in_array('count(c1)', array_values($selectedFields)));
    }

    /**
     * @covers ::getLogFileType
     */
    public function testGetLogFileType()
    {
        $fileType = $this->engine->getLogFileType('test.csv');
        self::assertEquals('csv', $fileType);

        $fileType = $this->engine->getLogFileType('test.log');
        self::assertEquals('access', $fileType);
    }
}
