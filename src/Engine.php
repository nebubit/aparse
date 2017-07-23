<?php

namespace AParse;

use AParse\Exceptions\InvalidArgumentException;

class Engine implements EngineInterface
{
    protected $fishPool;
    protected $selectedFields;
    protected $where;
    protected $limit;
    protected $fieldForCount;
    protected $fieldForGroupBy;
    protected $process;
    protected $lineString;
    protected $offset;

    // The total of valid items that will be returned in result
    protected $currentItemCount;

    // The result of the query
    protected $result;

    /**
     * Engine constructor.
     * @codeCoverageIgnore
     * @param $fishPool
     * @param ProcessQueryInterface $process
     * @param LineString $lineString
     */
    public function __construct($fishPool, ProcessQueryInterface $process, LineString $lineString)
    {
        $this->fishPool = $fishPool;
        $this->process = $process;
        $this->lineString = $lineString;
    }

    public function select(array $fields)
    {
        if (isset($fields[0]) && is_array($fields[0])) {
            $fields = $fields[0];
        }

        $this->selectedFields = $fields;

        return $this;
    }

    public function where(array $conditions)
    {
        $operator = '=';
        $whereArray = [];
        foreach ($conditions as $value) {
            $keyName = '';
            foreach ($value as $k => $v) {
                if (in_array(substr($k, -2), ['>=', '<='])) {
                    $operator = substr($k, -2);
                    $keyName = substr($k, 0, -2);
                } else if (substr($k, -1) == '=') {
                    $operator = substr($k, -1);
                    $keyName = substr($k, 0, -1);
                }

                $whereArray[] = [
                    'fieldName' => $keyName,
                    'fieldValue' => $v,
                    'operator' => $operator,
                ];
            }
        }

        $this->where = $whereArray;
        return $this;
    }

    /**
     * @todo if the fields does not an array
     * @param null $field
     * @return $this
     */
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

    /**
     * The same as group
     *
     * @codeCoverageIgnore
     * @param null $field
     * @return $this
     */
    public function groupBy($field = null)
    {
        return $this->group($field);
    }

    /**
     *  Group by
     *
     * @param null $field
     * @return Engine
     */
    public function group($field = null)
    {
        if (empty($field)) {
            return $this;
        }

        $this->fieldForGroupBy = $field[0];
        // Force the selectedFields to array if there is no selected fields
        if (!in_array($this->fieldForGroupBy, (array)$this->selectedFields)) {
            $this->selectedFields[] = $this->fieldForGroupBy;
        }

        return $this;
    }

    public function get($fields)
    {
        $limit = 1;
        $offset = 0;

        if (isset($fields[0])) {
            $limit = $fields[0];
        }
        if (isset($fields[1])) {
            $offset = $fields[1];
        }

        $this->limit = (int)$limit;
        $this->offset = (int)$offset;
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

                if ($this->currentItemCount > $this->offset) {
                    $this->result[$currentGroupKey] = $lineQueryResult;
                }
            } else {
                // For limit
                $this->currentItemCount++;

                if ($this->currentItemCount > $this->offset) {
                    $this->result[] = $lineQueryResult;
                }

                if ($this->currentItemCount >= ($this->limit + $this->offset)) {
                    break;
                }
            }
        }

        /**
         * There is no break in the group-by part above, so array_slice is needed.
         * This will also cause an issue that the group array may hit the memory limit.
         * Adding a count function in group part will fix this issue but I don't think this is necessary.
         * Because no one would like to make group on a long string column unless it's a mistake.
         */
        if (!is_null($this->fieldForGroupBy)) {
            $this->result = array_slice($this->result, 0, $this->limit);
        }

        fclose($fileHandle);
    }

    /**
     * @codeCoverageIgnore
     * {@inheritdoc}
     */
    public function getResult()
    {
        return $this->result;
    }
}
