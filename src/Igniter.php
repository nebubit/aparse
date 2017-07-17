<?php

namespace AParse;

class Igniter implements IgniterInterface
{
    private $fishPool;
    private $methods = [];
    private $processList = [];

    public static $endingMethods = [
        'get'
    ];

    public function __construct($fishPool)
    {
        $this->fishPool = $fishPool;
        $this->methods = get_class_methods($this);
    }

    /**
     * Reflecting methods to an ordered array
     *
     * @param $methodName
     * @param $arguments
     * @return $this|array
     */
    public function __call($methodName, $arguments)
    {
        if (!in_array($methodName, $this->methods)) {
            $this->processList[] = [
                'methodName' => $methodName,
                'arguments' => $arguments,
            ];
        }

        if (in_array($methodName, self::$endingMethods)) {
            return $this->processQuery($this->processList);
        }
        return $this;
    }

    /**
     * Return the query result data.
     *
     * @param array $processList
     * @return array
     */
    public function processQuery(array $processList = [])
    {
        $process = new \AParse\ProcessQuery();
        $lineString = new LineString();
        //Reset process list
        $this->processList = [];
        $engine = new Engine($this->fishPool, $process, $lineString);
        foreach ($processList as $value) {
            call_user_func([$engine, $value['methodName']], $value['arguments']);
        }

        return $engine->getResult();
    }
}
