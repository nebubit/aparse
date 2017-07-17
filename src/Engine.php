<?php

namespace AParse;

use AParse\Exceptions\InvalidArgumentException;

class Engine implements StringInterface, EngineInterface
{
    private $fishPool;
    private $selectedFields;
    private $where;
    private $limit;
    private $fieldForCount;
    private $fieldForGroupBy;
    private $process;

    // The total of valid items that will be returned in result
    private $currentItemCount;

    // The result of the query
    private $result;

    public function __construct($fishPool, ProcessInterface $process)
    {
        $this->fishPool = $fishPool;
        $this->process = $process;
    }

    public function accessLogLineToArray($lineString)
    {
        $temp = explode(' - - ', $lineString);
        if (count($temp) < 2) {
            return [];
        }
        $parsedLine[] = $temp[0];

        $otherStrings = $this->cutToPieces($temp[1]);

        return array_merge($parsedLine, $otherStrings);
    }

    public function select(array $fields)
    {
        $this->selectedFields = $fields;

        return $this;
    }

    public function where(array $conditions)
    {
        $operator = '=';
        $whereArray = [];
        foreach ($conditions as $value) {
            foreach ($value as $k => $v) {
                if (substr($k, -1) == '=') {
                    $operator = substr($k, -1);
                } else if (in_array(substr($k, -2), ['>=', '<='])) {
                    $operator = substr($k, -2);
                }

                $whereArray[] = [
                    'fieldName' => $k,
                    'fieldValue' => $v,
                    'operator' => $operator,
                ];
            }
        }

        $this->where = $whereArray;
        return $this;
    }

    public function count($field = null)
    {
        if (empty($field)) {
            return $this;
        }

        $this->fieldForCount = $field[0];
        // Add count column to select fields
        $this->selectedFields[] = 'count(' . $this->fieldForCount . ')';
        return $this;
    }

    public function groupBy($field = null)
    {
        if (empty($field)) {
            return $this;
        }

        $this->fieldForGroupBy = $field[0];
        if (!in_array($this->fieldForGroupBy, $this->selectedFields))
        $this->selectedFields[] = $this->fieldForGroupBy;
        return $this;
    }

    public function get($limit = null)
    {
        if (is_array($limit) && !empty($limit)) {
            $limit = $limit[0];
        }

        $this->limit = (int)$limit;
        $this->scanFile();
    }

    public function cutToPieces($string)
    {
        $string = trim($string);
        $result = [];

        if ($string == '') {
            return $result;
        }

        $separators = [
            ['[', ']'],
            ['"', '"'],
        ];

        $beginningChar = substr($string, 0, 1);

        $hit = false;
        foreach ($separators as $key => $value) {
            if ($beginningChar == $value[0]) {
                $hit = true;

                $remainedPart = substr($string, 1);
                $endPos = strpos($remainedPart, $value[1]);
                $result[] = substr($string, 1, $endPos);
                $remainedPart = substr($remainedPart, $endPos + 1);
                $otherStrings = $this->cutToPieces($remainedPart);
                $result = array_merge($result, $otherStrings);
            }
        }

        if (!$hit) {
            foreach ($separators as $key => $value) {
                $endPos = strpos($string, $value[0]);
                if ($endPos !== false) {
                    $firstPart = substr($string, 0, $endPos - 1);
                    $result = explode(' ', $firstPart);
                    $otherStrings = $this->cutToPieces(substr($string, $endPos));
                    $result = array_merge($result, $otherStrings);
                }
            }
        }
        return $result;
    }

    public function processLine(array $row)
    {
        //is aggregation query

        if (!empty($this->where)) {
            $check = $this->process->where($row, $this->where);
            if (!$check) {
                return [];
            }
        }

        if (!empty($this->fieldForCount)) {
            $row = $this->process->count($row, $this->fieldForCount);
        }

        $row = $this->process->select($row, $this->selectedFields);

        return $row;
    }

    public function scanFile()
    {
        $fileHandle = fopen($this->fishPool->currentFilePath, "r");
        if (!$fileHandle) {
            throw new InvalidArgumentException('Open file failed');
        }

        while (!feof($fileHandle)) {
            $line = fgets($fileHandle);
            $parsedLine = $this->accessLogLineToArray($line);
            $lineQueryResult = $this->processLine($parsedLine);
            if (empty($lineQueryResult)) {
                continue;
            }

            // For Group By
            if (!is_null($this->fieldForGroupBy)) {
                $currentGroupKey =
                    Process::getValuesByColumnNames($lineQueryResult, [$this->fieldForGroupBy]);
                if (empty($currentGroupKey)) {
                    continue;
                }

                // For limit
                $this->currentItemCount++;

                $currentGroupKey = array_values($currentGroupKey)[0];
                $this->result[$currentGroupKey] = $lineQueryResult;
            } else {
                // For limit
                $this->currentItemCount++;

                $this->result[] = $lineQueryResult;

                if ($this->currentItemCount >= $this->limit) {
                    break;
                }
            }
        }

        if (!is_null($this->fieldForGroupBy)) {
            $this->result = array_slice($this->result, 0, $this->limit);
        }

        fclose($fileHandle);
    }

    public function getResult()
    {
        return $this->result;
    }
}