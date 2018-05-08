<?php

namespace Nahid\JsonQ;

use Nahid\JsonQ\Exceptions\InvalidJsonException;
use Nahid\JsonQ\Exceptions\NullValueException;

class Jsonq
{
    use JsonQueriable;

    protected $_file;
    protected $_data = array();

    /*
        this constructor set main json file path
        otherwise create it and read file contents
        and decode as an array and store it in $this->_data
    */
    public function __construct($jsonFile = null)
    {
        $path = pathinfo($jsonFile);

        if (!isset($path['extension']) && !is_null($jsonFile)) {
            throw new InvalidJsonException();
        }

        if (!is_null($jsonFile) && isset($path['extension'])) {
            $this->import($jsonFile);
            $this->_file = $this->_path;
        }
    }

    public function from($node = null)
    {
        if (is_null($node) || $node == '') {
            throw new NullValueException("Null node exception");
        }

        if ($node == '.') {
            $this->_node = $node;

            return $this;
        }

        $this->_node = explode('.', $node);

        return $this;
    }

    public function prepare()
    {
        if (count($this->_andConditions) > 0 or count($this->_orConditions) > 0) {
            $calculatedData = $this->processConditions();

            unset($this->_andConditions);
            unset($this->_orConditions);
            $this->_node = '';
            $this->_andConditions = [];
            $this->_orConditions = [];

            return $this->collect($calculatedData);
        }

        return $this->collect($this->getData());
    }

    public function get($object = true)
    {
        if (is_null($this->_map) || is_string($this->_map)) {
            return $this->_map;
        }

        if (!$this->isMultiArray($this->_map)) {
            return (object) $this->_map;
        }
        $resultingData = [];
        foreach ($this->_map as $data) {
            if ($object) {
                $resultingData[] = (object) $data;
            } else {
                $resultingData[] = $data;
            }
        }

        return $resultingData;
    }

    public function count()
    {
        return count($this->_map);
    }

    public function sum($property = null)
    {
        $sum = 0;
        if (is_null($property)) {
            $sum = array_sum($this->_map);
        } else {
            foreach ($this->_map as $key => $val) {
                if (isset($val[$property])) {
                    if (is_numeric($val[$property])) {
                        $sum += $val[$property];
                    }
                }
            }
        }


        return $sum;
    }

    public function max($property = null)
    {
        if (is_null($property)) {
            $max = max($this->_map);
        } else {
            $max = max(array_column($this->_map, $property));
        }

        return $max;
    }

    public function min($property = null)
    {
        if (is_null($property)) {
            $min = min($this->_map);
        } else {
            $min = min(array_column($this->_map, $property));
        }

        return $min;
    }

    public function avg($column = null)
    {
        if (is_null($column)) {
            $total = array_sum($this->_map);
            $count = count($this->_map);
        } else {
            $total = $this->sum($column);
            $count = $this->count();
        }

        return ($total/$count);
    }

    public function first($object = true)
    {
        $data = $this->_map;
        if (count($data) > 0) {
            if ($object) {
                return json_decode(json_encode(reset($data)));
            }

            return json_decode(json_encode(reset($data)), true);
        }

        return null;
    }

    public function last($object = true)
    {
        $data = $this->_map;
        if (count($data) > 0) {
            if ($object) {
                return json_decode(json_encode(end($data)));
            }

            return json_decode(json_encode(end($data)), true);
        }

        return null;
    }

    public function nth($index, $object = true)
    {
        $data = $this->_map;
        $total_elm = count($data);
        $idx =  abs($index);
        $result = [];


        if (!is_integer($index) || $total_elm < $idx || $index == 0) {
            return null;
        }

        if ($index > 0) {
            $result = current($data);

            for ($i = 1; $i<$index; $i++) {
                $result = next($data);
            }

        } else {
            $result = end($data);

            for ($i = 1; $i < $idx; $i++) {
                $result = prev($data);
            }

        }

        if ($object) {
            return json_decode(json_encode($result));
        }

        return json_decode(json_encode($result), true);
    }

    public function sortAs($property, $order = 'asc')
    {
        if (!is_array($this->_map)) {
            return $this;
        }

        usort($this->_map, function ($a, $b) use ($property, $order) {
            $val1 = $a[$property];
            $val2 = $b[$property];
            if (is_string($val1)) {
                $val1 = strtolower($val1);
            }

            if (is_string($val2)) {
                $val2 = strtolower($val2);
            }

            if ($a[$property] == $b[$property]) {
                return 0;
            }
            $order = strtolower(trim($order));

            if ($order == 'desc') {
                return ($val1 > $val2) ? -1 : 1;
            } else {
                return ($val1 < $val2) ? -1 : 1;
            }
        });

        return $this;
    }

    public function find($path)
    {
        return $this->from($path)->prepare()->get();
    }


    public function each(callable $fn)
    {
        foreach ($this->_map as $key => $val) {
            $fn($key, $val);
        }
    }



    public function transform(callable $fn)
    {
        $new_data = [];
        foreach ($this->_map as $key => $val) {
            $new_data[$key] = $fn($val);
        }

        return $new_data;
    }


    public function filter(callable $fn, $key = false)
    {
        $new_data = [];
        foreach ($this->_map as $k => $val) {
            if ($fn($val)) {
                if ($key) {
                    $new_data[$k] = $val;
                } else {
                    $new_data[] = $val;
                }

            }
        }

        return $new_data;
    }

    public function then($node)
    {
        $this->_map = $this->prepare()->first(false);

        $this->from($node);

        return $this;
    }

    public function json($data)
    {
        if (is_string($data)) {
            if ($json = $this->isJson($data, true)) {
                return $this->collect($json);
            }
        }

        return $this;
    }

    public function collect($data)
    {
        $this->_map = $this->objectToArray($data);

        return $this;
    }

    protected function objectToArray($obj)
    {
        if (!is_array($obj) && !is_object($obj)) {
            return $obj;
        }

        if (is_object($obj)) {
            $obj = get_object_vars($obj);
        }

        return array_map([$this, 'objectToArray'], $obj);
    }

    public function implode($key, $delimiter = ',')
    {
        $implode = [];
        if (is_string($key)) {
            return $this->makeImplode($key, $delimiter);
        }

        if (is_array($key)) {
            foreach ($key as $k) {
                $imp = $this->makeImplode($k, $delimiter);
                $implode[$k] = $imp;
            }

            return $implode;
        }
        return '';
    }

    protected function makeImplode($key, $delimiter)
    {
        $data = array_column($this->_map, $key);

        if (is_array($data)) {
            return implode($delimiter, $data);
        }

        return null;
    }

    public function column($column)
    {
        return array_column($this->_map, $column);
    }

    public function toJson()
    {
        return json_encode($this->_map);
    }

    public function keys()
    {
        return array_keys($this->_map);
    }

    public function values()
    {
        return array_values($this->_map);
    }
}
