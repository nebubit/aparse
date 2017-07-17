<?php

namespace AParse;

class Igniter implements IgniterInterface
{
    private $fishPool;
    private $methods = [];
    private $processList = [];
    public static $queryOrder =[
        'select',
        'where',
    ];

    public static $endingMethod = [
        'get'
    ];

    public function __construct($fishPool)
    {
        $this->fishPool = $fishPool;
        $this->methods = get_class_methods($this);
    }

    public function __call($methodName, $arguments)
    {
        if (!in_array($methodName, $this->methods)){
            $this->processList[] = [
                'methodName' => $methodName,
                'arguments' => $arguments,
            ];
        }
        
        if (in_array($methodName, self::$endingMethod)){
            return $this->processQuery($this->processList);
        }
        return $this;
    }

    public function processQuery(array $processList = [])
    {
        $process = new \AParse\Process();
        //Reset process list
        $this->processList = [];
        $engine = new Engine($this->fishPool, $process);
        foreach ($processList as $value){
            call_user_func([$engine, $value['methodName']], $value['arguments']);
        }

        return $engine->getResult();
    }
}
