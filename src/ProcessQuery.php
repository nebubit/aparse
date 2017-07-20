<?php

namespace AParse;

class ProcessQuery implements ProcessQueryInterface
{
    public $aggregationCountNumber = [];

    public static function getValuesByColumnNames(array $row, array $columnNames)
    {
        $columnNames = array_flip($columnNames);
        $columnValues = array_intersect_key($row, $columnNames);

        return $columnValues;
    }

    public function select(array $row, array $fields)
    {
        if (empty($row)) {
            return [];
        }

        //TODO match * at any position in array
        if (trim($fields[0]) == '*') {
            return $row;
        }

        return self::getValuesByColumnNames($row, $fields);
    }

    public function where(array $row, array $conditions)
    {
        if (empty($conditions)) {
            return true;
        }

        if (empty($row)) {
            return false;
        }

        foreach ($conditions as $value) {
            $check = false;

            $columnForCompare = self::getValuesByColumnNames($row, [$value['fieldName']]);
            if (count($columnForCompare) < 1) {
                return false;
            }
            $actualValue = array_values($columnForCompare)[0];
            $expectedValue = $value['fieldValue'];
            switch ($value['operator']) {
                case '=':
                    $check = $actualValue == $expectedValue;
                    break;
                case '>=':
                    $check = $actualValue >= $expectedValue;
                    break;
                case '<=':
                    $check = $actualValue <= $expectedValue;
                    break;
            }

            if (!$check) {
                return $check;
            }
        }

        return true;
    }

    /**
     * Count
     *
     * @param array $row
     * @param $fieldForCount
     * @return array
     */
    public function count(array $row, $fieldForCount)
    {
        if ($fieldForCount == '*') {
            if (!isset($this->aggregationCountNumber['*'])) {
                $this->aggregationCountNumber['*'] = 0;
            }
            $this->aggregationCountNumber['*']++;
            $row['count(*)'] = &$this->aggregationCountNumber['*'];
            return $row;
        }

        // Get the value for counting
        // Not need to check the result,
        // a exception will be thrown if the key not exist.

        $columnValues = $this->getValuesByColumnNames($row, [$fieldForCount]);

        $keyForRow = 'count(' . $fieldForCount . ')';

        if (empty($columnValues)) {
            return $row;
        }

        $keyForAggregationCount = array_values($columnValues)[0];

        // Does the count field exist in the row.
        if (!isset($row[$keyForRow])) {
            // Does the count number for this field exist in the count list.
            if (!isset($this->aggregationCountNumber[$keyForAggregationCount])) {
                $this->aggregationCountNumber[$keyForAggregationCount] = 0;
            }

            $row[$keyForRow] =
                &$this->aggregationCountNumber[$keyForAggregationCount];
        }

        $this->aggregationCountNumber[$keyForAggregationCount]++;

        return $row;
    }
}
