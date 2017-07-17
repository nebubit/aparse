<?php

namespace AParse;

interface EngineInterface
{
    public function select(array $fields);

    public function where(array $conditions);

    public function count();

    public function get($limit);

    public function scanFile();
}
