<?php

namespace AParse;

class Process implements ProcessInterface
{
    private $aggregationCountNumber = [];

    public static function getValuesByColumnNames(array $row, array $columnNames)
    {
        $keys = array_map(function ($v) {
            if( (ctype_alpha(substr($v, 0,1)) && is_numeric(substr($v, 1,1))) ){
                return substr($v, 1);
            }

            return $v;
        }, $columnNames);

        $keys = array_flip($keys);

        return array_intersect_key($row, $keys);
    }

    public function select(array $row, array $fields)
    {
        if ($row == '') {
            return [];
        }

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

        return $check;
    }

    public function count(array $row, $fieldForCount)
    {
        // Count all
        if ($fieldForCount == '*') {

            if (!isset($this->aggregationCountNumber['*'])) {
                $this->aggregationCountNumber['*'] = 0;
            }
            $this->aggregationCountNumber['*']++;
            $row['count(*)'] = &$this->aggregationCountNumber['*'];
            return $row;
        }

        // Get the value for counting
        $columnValues = $this->getValuesByColumnNames($row, [$fieldForCount]);
        if (count($columnValues) < 1) {
            return $row;
        }
        $keyForRow = 'count(' . $fieldForCount . ')';
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
