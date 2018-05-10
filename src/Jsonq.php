<?php

namespace Nahid\JsonQ;

use Nahid\JsonQ\Exceptions\ConditionNotAllowedException;
use Nahid\JsonQ\Exceptions\InvalidJsonException;
use Nahid\JsonQ\Exceptions\InvalidNodeException;
use Nahid\JsonQ\Exceptions\NullValueException;

class Jsonq
{
    use JsonQueriable;



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
        }
    }

    /**
     * Deep copy current instance
     *
     * @return Jsonq
     */
    public function copy()
    {
        return clone $this;
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
        $this->_isProcessed = false;

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
     * getting prepared data
     *
     * @param bool $object
     * @return array|object
     * @throws ConditionNotAllowedException
     */
    public function get($object = true)
    {
        $this->prepare();

        if (is_null($this->_map) || is_string($this->_map)) {
            return $this->_map;
        }

        if (!$this->isMultiArray($this->_map)) {
            return (object) $this->_map;
        }
        $resultingData = [];
        foreach ($this->_map as $key=>$data) {
            if ($object) {
                $resultingData[$key] = (object) $data;
            } else {
                $resultingData[$key] = $data;
            }
        }

        return $resultingData;
    }


    /**
     * alias of get method
     *
     * @param bool $object
     * @return array|object
     * @throws ConditionNotAllowedException
     */
    public function fetch($object = true)
    {
        return $this->get($object);
    }



    /**
     * check data exists in system
     *
     * @return bool
     */
    public function exists()
    {
        return count($this->_map) > 0;
    }



    /**
     * reset given data to the $_map
     *
     * @param $data mixed
     * @return jsonq
     */
    public function reset($data = null)
    {
        if (!is_null($data)) {
            $this->_baseContents = $data;
        }

        $this->_map = $this->_baseContents;

        return $this;
    }


    /**
     * getting group data from specific column
     *
     * @param $column
     * @return $this
     * @throws InvalidNodeException
     * @throws ConditionNotAllowedException
     */
    public function groupBy($column)
    {
        if (count($this->_conditions) > 0) {
            $this->prepare();
        }

        $new_data = [];
        foreach ($this->_map as $map) {
            if (isset($map[$column])) {
                $new_data[$map[$column]][] = $map;
            }
        }

        $this->_map = $new_data;
        return $this;
    }

    /**
     * count prepared data
     *
     * @return int
     * @throws ConditionNotAllowedException
     */
    public function count()
    {
        $this->prepare();

        return count($this->_map);
    }

    /**
     * sum prepared data
     * @param $column int
     * @return int
     * @throws ConditionNotAllowedException
     */
    public function sum($column = null)
    {
        $this->prepare();

        $sum = 0;
        if (is_null($column)) {
            $sum = array_sum($this->_map);
        } else {
            foreach ($this->_map as $key => $val) {
                if (isset($val[$column])) {
                    if (is_numeric($val[$column])) {
                        $sum += $val[$column];
                    }
                }
            }
        }

        return $sum;
    }

    /**
     * getting max value from prepared data
     *
     * @param $column int
     * @return int
     * @throws ConditionNotAllowedException
     */
    public function max($column = null)
    {
        $this->prepare();

        if (is_null($column)) {
            $max = max($this->_map);
        } else {
            $max = max(array_column($this->_map, $column));
        }

        return $max;
    }

    /**
     * getting min value from prepared data
     *
     * @param $column int
     * @return string
     * @throws ConditionNotAllowedException
     */
    public function min($column = null)
    {
        $this->prepare();

        if (is_null($column)) {
            $min = min($this->_map);
        } else {
            $min = min(array_column($this->_map, $column));
        }

        return $min;
    }

    /**
     * getting average value from prepared data
     *
     * @param $column int
     * @return string
     * @throws ConditionNotAllowedException
     */
    public function avg($column = null)
    {
        $this->prepare();

        $count = $this->count();
        $total = $this->sum($column);

        return ($total/$count);
    }

    /**
     * getting first element of prepared data
     *
     * @param $object bool
     * @return object|array|null
     * @throws ConditionNotAllowedException
     */
    public function first($object = true)
    {
        $this->prepare();

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
     * @throws ConditionNotAllowedException
     */
    public function last($object = true)
    {
        $this->prepare();

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
     * @throws ConditionNotAllowedException
     */
    public function nth($index, $object = true)
    {
        $this->prepare();

        $data = $this->_map;
        $total_elm = count($data);
        $idx =  abs($index);

        if (!is_integer($index) || $total_elm < $idx || $index == 0 || !is_array($this->_map)) {
            return null;
        }

        if ($index > 0) {
            $result = $data[$index - 1];
        } else {
            $result = $data[$this->count() + $index];
        }

        if ($object) {
            return json_decode(json_encode($result));
        }

        return json_decode(json_encode($result), true);
    }

    /**
     * sorting from prepared data
     *
     * @param $column string
     * @param $order string
     * @return object|array|null
     * @throws ConditionNotAllowedException
     */
    public function sortAs($column, $order = 'asc')
    {
        $this->prepare();

        if (!is_array($this->_map)) {
            return $this;
        }

        usort($this->_map, function ($a, $b) use ($column, $order) {
            $val1 = $a[$column];
            $val2 = $b[$column];
            if (is_string($val1)) {
                $val1 = strtolower($val1);
            }

            if (is_string($val2)) {
                $val2 = strtolower($val2);
            }

            if ($a[$column] == $b[$column]) {
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
     * @throws ConditionNotAllowedException
     */
    public function find($path)
    {
        return $this->from($path)->prepare()->get();
    }

    /**
     * take action of each element of prepared data
     *
     * @param $fn callable
     * @throws ConditionNotAllowedException
     */
    public function each(callable $fn)
    {
        $this->prepare();

        foreach ($this->_map as $key => $val) {
            $fn($key, $val);
        }
    }

    /**
     * transform prepared data by using callable function
     *
     * @param $fn callable
     * @return object|array
     * @throws ConditionNotAllowedException
     */
    public function transform(callable $fn)
    {
        $this->prepare();

        $new_data = [];
        foreach ($this->_map as $key => $val) {
            $new_data[$key] = $fn($val);
        }

        return $new_data;
    }

    /**
     * pipe send output in next pipe
     *
     * @param $fn callable
     * @param $class string|null
     * @return object|array
     * @throws ConditionNotAllowedException
     */
    public function pipe(callable $fn, $class = null)
    {
        $this->prepare();

        if (is_string($fn) && !is_null($class)) {
            $instance = new $class;

            $this->_map = call_user_func_array([$instance, $fn], [$this]);
            return $this;
        }

        $this->_map = $fn($this);
        return $this;
    }


    /**
     * filtered each element of prepared data
     *
     * @param $fn callable
     * @param $key bool
     * @return object|array
     * @throws ConditionNotAllowedException
     */
    public function filter(callable $fn, $key = false)
    {
        $this->prepare();

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
     * then method set position of working data
     *
     * @param $node string
     * @return jsonq
     * @throws NullValueException
     * @throws ConditionNotAllowedException
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
        $this->_baseContents = &$this->_map;

        return $this;
    }


    /**
     * implode resulting data from desire key and delimeter
     *
     * @param $key string|array
     * @param $delimiter string
     * @return string|array
     * @throws ConditionNotAllowedException
     */
    public function implode($key, $delimiter = ',')
    {
        $this->prepare();

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
     * @throws ConditionNotAllowedException
     */
    public function column($column)
    {
        $this->prepare();

        return array_column($this->_map, $column);
    }

    /**
     * getting raw JSON from prepared data
     *
     * @return string
     * @throws ConditionNotAllowedException
     */
    public function toJson()
    {
        $this->prepare();

        return json_encode($this->_map);
    }

    /**
     * getting all keys from prepared data
     *
     * @return object|array
     * @throws ConditionNotAllowedException
     */
    public function keys()
    {
        $this->prepare();

        return array_keys($this->_map);
    }

    /**
     * getting all values from prepared data
     *
     * @return object|array
     * @throws ConditionNotAllowedException
     */
    public function values()
    {
        $this->prepare();

        return array_values($this->_map);
    }

    /**
     * getting chunk values from prepared data
     *
     * @param $amount
     * @param $fn
     * @return object|array|bool
     * @throws ConditionNotAllowedException
     */
    public function chunk($amount, callable $fn = null)
    {
        $this->prepare();

        $chunk_value = array_chunk($this->_map, $amount);
        $chunks = [];

        if (!is_null($fn) && is_callable($fn)) {
            foreach ($chunk_value as $chunk) {
                $return = $fn($chunk);
                if (!is_null($return)) {
                    $chunks[] = $return;
                }
            }
            return count($chunks)>0?$chunks:null;
        }

        return $chunk_value;
    }
}
