<?php

namespace Nahid\JsonQ;

use Nahid\QArray\Exceptions\ConditionNotAllowedException;
use Nahid\QArray\Exceptions\InvalidJsonException;
use Nahid\QArray\Exceptions\FileNotFoundException;
use Nahid\QArray\QueryEngine;

class Jsonq extends QueryEngine
{
    /**
     * @param string $jsonFile
     * @return array|bool|string
     * @throws FileNotFoundException
     * @throws InvalidJsonException
     */
    public function readPath($jsonFile)
    {
        if (!is_null($jsonFile)) {
            $path = pathinfo($jsonFile);
            $extension = isset($path['extension']) ? $path['extension'] : null;

            if ($extension != 'json') {
                throw new InvalidJsonException();
            }

            if (is_string($jsonFile) && file_exists($jsonFile)) {
                return $this->getDataFromFile($jsonFile);

            }

            throw new FileNotFoundException();
        }
    }

    /**
     * Read JSON data from file
     *
     * @param string $file
     * @param string $type
     * @return bool|string|array
     * @throws FileNotFoundException
     * @throws InvalidJsonException
     */
    protected function getDataFromFile($file, $type = 'application/json')
    {
        if (file_exists($file)) {
            $opts = [
                'http' => [
                    'header' => 'Content-Type: '.$type.'; charset=utf-8',
                ],
            ];

            $context = stream_context_create($opts);
            $data = file_get_contents($file, 0, $context);
            $json = $this->isJson($data, true);

            if (!$json) {
                throw new InvalidJsonException();
            }

            return $json;
        }

        throw new FileNotFoundException();
    }

    /**
     * Sort prepared data using a custom sort function.
     *
     * @param callable $sortFunc
     *
     * @return object|array|null
     * @throws ConditionNotAllowedException
     */
    public function sortByCallable(callable $sortFunc)
    {
        $this->prepare();

        if (!is_array($this->_map)) {
            return $this;
        }

        usort($this->_map, $sortFunc);

        return $this;
    }

    /**
     * import raw JSON data for process
     *
     * @param string $data
     * @return array
     */
    public function parseData($data)
    {
        $json = $this->isJson($data, true);

        return $json;
    }

}
