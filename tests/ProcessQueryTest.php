<?php

namespace Tests;

use function AParse\dd;
use AParse\ProcessQuery;

/**
 * @coversDefaultClass \AParse\ProcessQuery
 */
class ProcessQueryTest extends BaseTest
{
    public $processQuery;
    public $row = ['c0' => 'value1', 'c1' => 'value2', 'c2' => 'value3', 'c3' => 'value4'];
    public $conditions = [
        [
            'fieldName' => 'c1',
            'fieldValue' => 'value2',
            'operator' => '=',
        ]
    ];


    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->processQuery = new ProcessQuery();
    }

    /**
     * @covers ::getValuesByColumnNames
     */
    public function testGetValuesByColumnNames()
    {
        $columnNames = ['c1', 'c3'];
        $expectedResult = [
            'c1' => 'value2',
            'c3' => 'value4',
        ];

        $data = ProcessQuery::getValuesByColumnNames($this->row, $columnNames);
        self::assertTrue($expectedResult == $data);
    }

    /**
     * @covers ::select
     */
    public function testSelectWithEmptyLineRow()
    {
        $data = $this->processQuery->select($row = [], []);
        self::assertTrue($data == []);
    }

    /**
     * @covers ::select
     */
    public function testSelectWithAllFields()
    {
        $data = $this->processQuery->select($this->row, ['*']);
        self::assertTrue($data == $this->row);
    }

    /**
     * @covers ::select
     */
    public function testSelectWithFields()
    {
        $columnNames = ['c1', 'c2'];
        $data = $this->processQuery->select($this->row, $columnNames);
        $expectedResult = ProcessQuery::getValuesByColumnNames($this->row, $columnNames);

        self::assertTrue($expectedResult == $data);
    }

    /**
     * @covers ::where
     */
    public function testWhereWithoutCondition()
    {
        $data = $this->processQuery->where($this->row, []);
        self::assertTrue($data);
    }

    /**
     * @covers ::where
     */
    public function testWhereWithEmptyRow()
    {
        $data = $this->processQuery->where([], $this->conditions);
        self::assertFalse($data);
    }

    /**
     * @covers ::where
     */
    public function testWhereWithUnmatchedCondition()
    {
        $conditions = $this->conditions;
        $conditions[0]['fieldValue'] = 'value';
        $data = $this->processQuery->where($this->row, $conditions);
        self::assertFalse($data);
    }

    /**
     * @covers ::where
     */
    public function testWhereWithMatchedCondition()
    {
        $conditions = $this->conditions;
        $data = $this->processQuery->where($this->row, $conditions);
        self::assertTrue($data);
    }

    /**
     * @covers ::count
     */
    public function testCountAll()
    {
        $processQuery = new ProcessQuery();
        $processQuery->count($this->row, '*');
        self::assertEquals(1, $processQuery->aggregationCountNumber['*']);

        $processQuery->count($this->row, '*');
        self::assertEquals(2, $processQuery->aggregationCountNumber['*']);
    }

    /**
     * @covers ::count
     */
    public function testCountWithFieldMatched()
    {
        $processQuery = new ProcessQuery();
        $processQuery->count($this->row, 'c3');
        self::assertEquals(1, $processQuery->aggregationCountNumber[$this->row['c3']]);

        $processQuery->count($this->row, 'c3');
        self::assertEquals(2, $processQuery->aggregationCountNumber[$this->row['c3']]);
    }
}
