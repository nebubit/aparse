<?php

namespace AParse;

interface ProcessQueryInterface
{
    /**
     * Select specific fields in a row.
     *
     * @param array $row
     * @param array $fields
     * @return array
     */
    public function select(array $row, array $fields);

    /**
     * Check the conditions for a row.
     *
     * @param array $row
     * @param array $conditions
     * @return bool
     */
    public function where(array $row, array $conditions);

    /**
     * Count the total number of the rows
     * then put the value into the raw and return the row itself.
     *
     * @param array $row
     * @param $fieldForCount
     * @return array
     */
    public function count(array $row, $fieldForCount);
}
