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

    public function setUp()
    {
        parent::setUp();

        $process = new ProcessQuery();
        $lineString = new LineString();
        $fishPool = new FishPool();
        $this->engine = new Engine($fishPool, $process, $lineString);
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

        $expected = [
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
        self::assertTrue($expected == $data);
    }

    /**
     * @covers ::count
     */
    public function testCount()
    {
        $field = 'c1';
        $expected = ['count(c)'];
        $this->engine->count($field);
        $data = $this->getReflectionProperty($this->engine, 'selectedFields');

        self::assertTrue($expected == $data);
    }
}
