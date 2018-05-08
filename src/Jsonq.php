<?php

namespace Nahid\JsonQ;

use Nahid\JsonQ\Exceptions\InvalidJsonException;
use Nahid\JsonQ\Exceptions\NullValueException;

class Jsonq
{
    use JsonQueriable;

    /**
     * store file's realpath
     * @var string
     */
    protected $_file;


    /**
     * Jthis constructor set main json file path
     * otherwise create it and read file contents
     * and decode as an array and store it in $this->_data
     *
     * @param null $jsonFile
     * @throws Exceptions\FileNotFoundException
     * @throws InvalidJsonException
     */
    public function __construct($jsonFile = null)
    {
        if (!is_null($jsonFile)) {
            $path = pathinfo($jsonFile);

            if ($path['extension'] != 'json') {
                throw new InvalidJsonException();
            }
        }

        if (!is_null($jsonFile) && isset($path['extension'])) {
            $this->import($jsonFile);
            $this->_file = $this->_path;
        }
    }

    /**
     * Set node path, where JsonQ start to prepare
     *
     * @param null $node
     * @return $this
     * @throws NullValueException
     */
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

    /**
     * Prepare data from desire conditions
     * @return Jsonq
     */
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

    /**
     * getting prepared data
     *
     * @param bool $object
     * @return array|object
     */
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

    /**
     * count prepared data
     *
     * @return int
     */
    public function count()
    {
        return count($this->_map);
    }

    /**
     * sum prepared data
     * @param $property int
     * @return int
     */
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

    /**
     * getting max value from prepared data
     *
     * @param $property int
     * @return int
     */
    public function max($property = null)
    {
        if (is_null($property)) {
            $max = max($this->_map);
        } else {
            $max = max(array_column($this->_map, $property));
        }

        return $max;
    }

    /**
     * getting min value from prepared data
     *
     * @param $property int
     * @return string
     */
    public function min($property = null)
    {
        if (is_null($property)) {
            $min = min($this->_map);
        } else {
            $min = min(array_column($this->_map, $property));
        }

        return $min;
    }

    /**
     * getting average value from prepared data
     *
     * @param $column int
     * @return string
     */
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

    /**
     * getting first element of prepared data
     *
     * @param $object bool
     * @return object|array|null
     */
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

    /**
     * getting last element of prepared data
     *
     * @param $object bool
     * @return object|array|null
     */
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

    /**
     * getting nth number of element of prepared data
     *
     * @param $index int
     * @param $object bool
     * @return object|array|null
     */
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

    /**
     * sorting from prepared data
     *
     * @param $property string
     * @param $order string
     * @return object|array|null
     */
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

    /**
     * getting data from desire path
     *
     * @param $path string
     * @return mixed
     * @throws NullValueException
     */
    public function find($path)
    {
        return $this->from($path)->prepare()->get();
    }

    /**
     * take action of each element of prepared data
     *
     * @param $fn callable
     */
    public function each(callable $fn)
    {
        foreach ($this->_map as $key => $val) {
            $fn($key, $val);
        }
    }

    /**
     * transform prepared data by using callable function
     *
     * @param $fn callable
     * @return object|array
     */
    public function transform(callable $fn)
    {
        $new_data = [];
        foreach ($this->_map as $key => $val) {
            $new_data[$key] = $fn($val);
        }

        return $new_data;
    }


    /**
     * filtered each element of prepared data
     *
     * @param $fn callable
     * @param $key bool
     * @return object|array
     */
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

    /**
     * then method set possion of working data
     *
     * @param $node string
     * @return jsonq
     * @throws NullValueException
     */
    public function then($node)
    {
        $this->_map = $this->prepare()->first(false);

        $this->from($node);

        return $this;
    }

    /**
     * import raw JSON data for process
     *
     * @param $data string
     * @return jsonq
     */
    public function json($data)
    {
        if (is_string($data)) {
            if ($json = $this->isJson($data, true)) {
                return $this->collect($json);
            }
        }

        return $this;
    }

    /**
     * import parsed data from raw json
     *
     * @param $data array|object
     * @return jsonq
     */
    public function collect($data)
    {
        $this->_map = $this->objectToArray($data);

        return $this;
    }

    /**
     * parse object to array
     *
     * @param $obj object
     * @return array|mixed
     */
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

    /**
     * implode resulting data from desire key and delimeter
     *
     * @param $key string|array
     * @param $delimiter string
     * @return string|array
     */
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

    /**
     * process implode from resulting data
     *
     * @param $key string
     * @param $delimiter string
     * @return string|null
     */
    protected function makeImplode($key, $delimiter)
    {
        $data = array_column($this->_map, $key);

        if (is_array($data)) {
            return implode($delimiter, $data);
        }

        return null;
    }

    /**
     * getting specific key's value from prepared data
     *
     * @param $column string
     * @return object|array
     */
    public function column($column)
    {
        return array_column($this->_map, $column);
    }

    /**
     * getting raw JSON from prepared data
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->_map);
    }

    /**
     * getting all keys from prepared data
     *
     * @return object|array
     */
    public function keys()
    {
        return array_keys($this->_map);
    }

    /**
     * getting all values from prepared data
     *
     * @return object|array
     */
    public function values()
    {
        return array_values($this->_map);
    }
}
