<?php

namespace AParse;

class LineString implements LineStringInterface
{
    public function parseLineToArray($lineString, $type)
    {
        switch ($type) {
            case 'access':
                $result = $this->parseAccessLogLineToArray($lineString);
                break;
            case 'csv':
                $result = $this->parseCsvLineToArray($lineString);
                break;
            default:
                $result = $this->parseLineByDefaultRule($lineString);
        }

        return $result;
    }

    public function parseLineByDefaultRule($lineString)
    {
        return explode(' ', $lineString);
    }

    /**
     * parse a line string of CSV file.
     *
     * @param string $lineString
     * @return array line string to array
     */
    public function parseCsvLineToArray($lineString)
    {
        return explode(',', $lineString);
    }

    /**
     * parse a line string of Apache access log.
     *
     * @param string $lineString
     * @return array line string to array
     */
    public function parseAccessLogLineToArray($lineString)
    {
        $temp = explode(' - - ', $lineString);
        if (count($temp) < 2) {
            return [];
        }
        $parsedLine[] = $temp[0];

        $otherStrings = $this->cutAccessLogToPieces($temp[1]);

        $data = array_merge($parsedLine, $otherStrings);

        $keys = range(0, count($data) - 1);
        $keys = array_map(function ($v) {
            return ProcessQueryInterface::KEY_PREFIX . $v;
        }, $keys);

        return array_combine($keys, $data);
    }

    public function cutAccessLogToPieces($string)
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
                $otherStrings = $this->cutAccessLogToPieces($remainedPart);
                $result = array_merge($result, $otherStrings);
            }
        }

        if (!$hit) {
            foreach ($separators as $key => $value) {
                $endPos = strpos($string, $value[0]);
                if ($endPos !== false) {
                    $firstPart = substr($string, 0, $endPos - 1);
                    $result = explode(' ', $firstPart);
                    $otherStrings = $this->cutAccessLogToPieces(substr($string, $endPos));
                    $result = array_merge($result, $otherStrings);
                }
            }
        }
        return $result;
    }
}
