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
     * this constructor set main json file path
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
            $extension = isset($path['extension']) ? $path['extension'] : null;
            
            if ($extension != 'json') {
                throw new InvalidJsonException();
            }
            
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

        $this->_node = $node;

        return $this;
    }

    /**
     * Alias of from() method
     *
     * @param null $node
     * @return $this
     * @throws NullValueException
     */
    public function at($node = null)
    {
        return $this->from($node);
    }

    /**
     * select desired column
     *
     * @param ... scalar
     * @return $this
     */
    public function select()
    {
        $args = func_get_args();
        if (count($args) > 0 ){
            $this->_select = $args;
        }

        return $this;
    }

    /**
     * select desired column for except
     *
     * @param ... scalar
     * @return $this
     */
    public function except()
    {
        $args = func_get_args();
        if (count($args) > 0 ){
            $this->_except = $args;
        }

        return $this;
    }

    /**
     * getting prepared data
     *
     * @param bool $object
     * @return array|object
     * @throws ConditionNotAllowedException
     */
    public function get($object = false)
    {
        $this->prepare();

        return $this->prepareResult($this->_data, $object);
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
     * @throws ConditionNotAllowedException
     */
    public function exists()
    {
        $this->prepare();

        return (!empty($this->_data) && !is_null($this->_data));
    }

    /**
     * reset given data to the $_data
     *
     * @param mixed $data
     * @param bool $instance
     * @return jsonq
     */
    public function reset($data = null, $instance = false)
    {
        if (!is_null($data)) {
            $this->_baseContents = $data;
        }

        if ($instance) {
            $self = new self();
            $self->collect($this->_baseContents);

            return $self;
        }

        $this->_data = $this->_baseContents;
        $this->reProcess();

        return $this;
    }

    /**
     * getting group data from specific column
     *
     * @param string $column
     * @return $this
     * @throws ConditionNotAllowedException
     */
    public function groupBy($column)
    {
        $this->prepare();

        $data = [];
        foreach ($this->_data as $map) {
            $value = $this->getFromNested($map, $column);
            if ($value) {
                $data[$value][] = $map;
            }
        }

        $this->_data = $data;
        return $this;
    }

    public function countGroupBy($column)
    {

        $this->prepare();

        $data = [];
        foreach ($this->_data as $map) {
            $value = $this->getFromNested($map, $column);
            if (!$value) {
                continue;
            }

            if (isset($data[$value])) {
                $data[$value]  ++;
            } else {
                $data[$value] = 1;
            }
        }

        $this->_data = $data;
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

        return count($this->_data);
    }

    /**
     * size is an alias of count
     *
     * @return int
     * @throws ConditionNotAllowedException
     */
    public function size()
    {
        return $this->count();
    }

    /**
     * sum prepared data
     * @param int $column
     * @return int
     * @throws ConditionNotAllowedException
     */
    public function sum($column = null)
    {
        $this->prepare();

        $sum = 0;
        if (is_null($column)) {
            $sum = array_sum($this->_data);
        } else {
            foreach ($this->_data as $key => $val) {
                $value = $this->getFromNested($val, $column);
                if (is_scalar($value)) {
                    $sum += $value;
                }

            }
        }

        return $sum;
    }

    /**
     * getting max value from prepared data
     *
     * @param int $column
     * @return int
     * @throws ConditionNotAllowedException
     */
    public function max($column = null)
    {
        $this->prepare();

        if (is_null($column)) {
            $max = max($this->_data);
        } else {
            $max = max(array_column($this->_data, $column));
        }

        return $max;
    }

    /**
     * getting min value from prepared data
     *
     * @param int $column
     * @return string
     * @throws ConditionNotAllowedException
     */
    public function min($column = null)
    {
        $this->prepare();

        if (is_null($column)) {
            $min = min($this->_data);
        } else {
            $min = min(array_column($this->_data, $column));
        }

        return $min;
    }

    /**
     * getting average value from prepared data
     *
     * @param int $column
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
     * @param bool $object
     * @return object|array|null
     * @throws ConditionNotAllowedException
     */
    public function first($object = false)
    {
        $this->prepare();

        $data = $this->_data;
        if (count($data) > 0) {
            return $this->prepareResult(reset($data), $object);
        }

        return null;
    }

    /**
     * getting last element of prepared data
     *
     * @param bool $object
     * @return object|array|null
     * @throws ConditionNotAllowedException
     */
    public function last($object = false)
    {
        $this->prepare();

        $data = $this->_data;
        if (count($data) > 0) {
            return $this->prepareResult(end($data), $object);
        }

        return null;
    }

    /**
     * getting nth number of element of prepared data
     *
     * @param int $index
     * @param bool $object
     * @return object|array|null
     * @throws ConditionNotAllowedException
     */
    public function nth($index, $object = false)
    {
        $this->prepare();

        $data = $this->_data;
        $total_elm = count($data);
        $idx =  abs($index);

        if (!is_integer($index) || $total_elm < $idx || $index == 0 || !is_array($this->_data)) {
            return null;
        }

        if ($index > 0) {
            $result = $data[$index - 1];
        } else {
            $result = $data[$this->count() + $index];
        }

        return $this->prepareResult($result, $object);
    }

    /**
     * sorting from prepared data
     *
     * @param string $column
     * @param string $order
     * @return object|array|null
     * @throws ConditionNotAllowedException
     */
    public function sortBy($column, $order = 'asc')
    {
        $this->prepare();

        if (!is_array($this->_data)) {
            return $this;
        }

        usort($this->_data, function ($a, $b) use ($column, $order) {
            $val1 = $this->getFromNested($a, $column);
            $val2 = $this->getFromNested($b, $column);
            if (is_string($val1)) {
                $val1 = strtolower($val1);
            }

            if (is_string($val2)) {
                $val2 = strtolower($val2);
            }

            if ($val1 == $val2) {
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

        if (!is_array($this->_data)) {
            return $this;
        }

        usort($this->_data, $sortFunc);

        return $this;
    }

    /**
     * Sort an array value
     *
     * @param string $order
     * @return Jsonq
     */
    public function sort($order = 'asc')
    {
        if ($order == 'desc') {
            rsort($this->_data);
        }else{
            sort($this->_data);
        }

        return $this;
    }

    /**
     * getting data from desire path
     *
     * @param string $path
     * @param bool $object
     * @return mixed
     * @throws NullValueException
     * @throws ConditionNotAllowedException
     */
    public function find($path, $object = false)
    {
        return $this->from($path)->prepare()->get($object);
    }

    /**
     * take action of each element of prepared data
     *
     * @param callable $fn
     * @throws ConditionNotAllowedException
     */
    public function each(callable $fn)
    {
        $this->prepare();

        foreach ($this->_data as $key => $val) {
            $fn($key, $val);
        }
    }

    /**
     * transform prepared data by using callable function
     *
     * @param callable $fn
     * @return object|array
     * @throws ConditionNotAllowedException
     */
    public function transform(callable $fn)
    {
        $this->prepare();

        $new_data = [];
        foreach ($this->_data as $key => $val) {
            $new_data[$key] = $fn($val);
        }

        return $this->prepareResult($new_data, false);
    }

    /**
     * pipe send output in next pipe
     *
     * @param callable $fn
     * @param string|null $class
     * @return object|array
     * @throws ConditionNotAllowedException
     */
    public function pipe(callable $fn, $class = null)
    {
        $this->prepare();

        if (is_string($fn) && !is_null($class)) {
            $instance = new $class;

            $this->_data = call_user_func_array([$instance, $fn], [$this]);
            return $this;
        }

        $this->_data = $fn($this);
        return $this;
    }

    /**
     * filtered each element of prepared data
     *
     * @param callable $fn
     * @param bool $key
     * @return mixed|array
     * @throws ConditionNotAllowedException
     */
    public function filter(callable $fn, $key = false)
    {
        $this->prepare();

        $data = [];
        foreach ($this->_data as $k => $val) {
            if ($fn($val)) {
                if ($key) {
                    $data[$k] = $val;
                } else {
                    $data[] = $val;
                }
            }
        }

        return $this->prepareResult($data, false);
    }

    /**
     * then method set position of working data
     *
     * @param string $node
     * @return jsonq
     * @throws NullValueException
     * @throws ConditionNotAllowedException
     */
    public function then($node)
    {
        $this->_data = $this->prepare()->first(false);

        $this->from($node);

        return $this;
    }

    /**
     * import raw JSON data for process
     *
     * @param string $data
     * @return jsonq
     */
    public function json($data)
    {
        $json = $this->isJson($data, true);

        if ($json) {
            return $this->collect($json);
        }

        return $this;
    }

    /**
     * import parsed data from raw json
     *
     * @param array|object $data
     * @return jsonq
     */
    public function collect($data)
    {
        $this->_data = $this->objectToArray($data);
        $this->_baseContents = &$this->_data;

        return $this;
    }

    /**
     * implode resulting data from desire key and delimeter
     *
     * @param string|array $key
     * @param string $delimiter
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
     * @param string $key
     * @param string $delimiter
     * @return string|null
     */
    protected function makeImplode($key, $delimiter)
    {
        $data = array_column($this->_data, $key);

        if (is_array($data)) {
            return implode($delimiter, $data);
        }

        return null;
    }

    /**
     * getting specific key's value from prepared data
     *
     * @param string $column
     * @return object|array
     * @throws ConditionNotAllowedException
     */
    public function column($column)
    {
        $this->prepare();

        return array_column($this->_data, $column);
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

        return json_encode($this->_data);
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

        return array_keys($this->_data);
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

        return array_values($this->_data);
    }

    /**
     * getting chunk values from prepared data
     *
     * @param int $amount
     * @param $fn
     * @return object|array|bool
     * @throws ConditionNotAllowedException
     */
    public function chunk($amount, callable $fn = null)
    {
        $this->prepare();

        $chunk_value = array_chunk($this->_data, $amount);
        $chunks = [];

        if (!is_null($fn) && is_callable($fn)) {
            foreach ($chunk_value as $chunk) {
                $return = $fn($chunk);
                if (!is_null($return)) {
                    $chunks[] = $return;
                }
            }
            return count($chunks) > 0 ? $chunks : null;
        }

        return $chunk_value;
    }
}
