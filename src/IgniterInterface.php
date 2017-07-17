<?php

namespace AParse;

interface IgniterInterface
{
    /**
     * Return the query result data.
     *
     * Processing the query string.
     *
     * @param array $processList
     * @return array
     */
    public function processQuery(array $processList = []);
}
