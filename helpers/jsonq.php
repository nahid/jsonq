<?php

if (!function_exists('jsonq')) {
    function jsonq($jsonData)
    {
        if (!is_string($jsonData)) throw new \Nahid\QArray\Exceptions\InvalidJsonException();

        $json = new Nahid\JsonQ\Jsonq();
        return $json->collect($json->parseData($jsonData));
    }
}
