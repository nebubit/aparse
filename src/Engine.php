<?php

namespace AParse;

use AParse\Exceptions\InvalidArgumentException;

class Engine implements EngineInterface
{
    private $fishPool;
    private $selectedFields;
    private $where;
    private $limit;
    private $fieldForCount;
    private $fieldForGroupBy;
    private $process;
    private $lineString;

    // The total of valid items that will be returned in result
    private $currentItemCount;

    // The result of the query
    private $result;

    public function __construct($fishPool, ProcessQueryInterface $process, LineString $lineString)
    {
        $this->fishPool = $fishPool;
        $this->process = $process;
        $this->lineString = $lineString;
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
        if (!in_array($this->fieldForGroupBy, $this->selectedFields)) {
            $this->selectedFields[] = $this->fieldForGroupBy;
        }

        return $this;
    }

    /**
     * The same as groupBy
     *
     * @param null $field
     * @return Engine
     */
    public function group($field = null)
    {
        return $this->groupBy($field);
    }

    public function get($limit = null)
    {
        if (is_array($limit) && !empty($limit)) {
            $limit = $limit[0];
        }

        $this->limit = (int)$limit;
        $this->scanFile();
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

    /**
     * Get file type.
     *
     * @return string
     */
    public function getLogFileType($fileFullPath)
    {
        $fileType = pathinfo($fileFullPath, PATHINFO_EXTENSION);
        switch (strtolower($fileType)) {
            case 'csv':
                return 'csv';
            default:
                return 'access';
        }
    }

    public function scanFile()
    {
        $fileFullPath = $this->fishPool->currentFilePath;
        $fileType = $this->getLogFileType($fileFullPath);

        $fileHandle = fopen($fileFullPath, "r");
        if (!$fileHandle) {
            throw new InvalidArgumentException('Open file failed');
        }

        while (!feof($fileHandle)) {
            $line = fgets($fileHandle);
            $parsedLine = $this->lineString->parseLineToArray($line, $fileType);
            $lineQueryResult = $this->processLine($parsedLine);
            if (empty($lineQueryResult)) {
                continue;
            }

            // For Group By
            if (!is_null($this->fieldForGroupBy)) {
                $currentGroupKey =
                    ProcessQuery::getValuesByColumnNames($lineQueryResult, [$this->fieldForGroupBy]);
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
