<?php

namespace AParse;

interface EngineInterface
{
    public function select(array $fields);

    public function where(array $conditions);

    public function count();

    public function get($fields);

    public function group($field = null);

    public function scanFile();

    /**
     * Get query result
     *
     * @return array
     */
    public function getResult();

    public function filter();
}
