<?php

namespace AParse;

interface ProcessInterface
{
    public function select(array $row, array $fields);
    public function where(array $row, array $conditions);
    public function count(array $row, $fieldForCount);
}