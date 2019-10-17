<?php

namespace Nahid\JsonQ;

use Nahid\JsonQ\Exceptions\ConditionNotAllowedException;
use Nahid\JsonQ\Exceptions\FileNotFoundException;
use Nahid\JsonQ\Exceptions\InvalidJsonException;
use Nahid\JsonQ\Results\ValueNotFound;
use Nahid\JsonQ\Condition;

trait JsonQueriable
{
    /**
     * store node path
     * @var string|array
     */
    protected $_node = '';

    /**
     * contain prepared data for process
     * @var mixed
     */
    protected $_map;

    /**
     * contains column names
     * @var array
     */
    protected $_select = [];

    /**
     * contains column names for except
     * @var array
     */
    protected $_except = [];

    /**
     * Stores base contents.
     *
     * @var array
     */
    protected $_baseContents = [];

    /**
     * Stores all conditions.
     *
     * @var array
     */
    protected $_conditions = [];

    /**
     * @var bool
     */
    protected $_isProcessed = false;

    /**
     * map all conditions with methods
     * @var array
     */
    protected static $_rulesMap = [
        '=' => 'equal',
        'eq' => 'equal',
        '==' => 'strictEqual',
        'seq' => 'strictEqual',
        '!=' => 'notEqual',
        'neq' => 'notEqual',
        '!==' => 'strictNotEqual',
        'sneq' => 'strictNotEqual',
        '>' => 'greaterThan',
        'gt' => 'greaterThan',
        '<' => 'lessThan',
        'lt' => 'lessThan',
        '>=' => 'greaterThanOrEqual',
        'gte' => 'greaterThanOrEqual',
        '<=' => 'lessThanOrEqual',
        'lte' => 'lessThanOrEqual',
        'in'    => 'in',
        'notin' => 'notIn',
        'null' => 'isNull',
        'notnull' => 'isNotNull',
        'startswith' => 'startWith',
        'endswith' => 'endWith',
        'match' => 'match',
        'contains' => 'contains',
        'dates' => 'dateEqual',
        'month' => 'monthEqual',
        'year' => 'yearEqual',
    ];


    /**
     * import data from file
     *
     * @param string|null $file
     * @return bool
     * @throws FileNotFoundException
     * @throws InvalidJsonException
     */
    public function import($file = null)
    {
        if (!is_null($file)) {
            if (is_string($file) && file_exists($file)) {
                $this->_map = $this->getDataFromFile($file);
                $this->_baseContents = $this->_map;
                return true;
            }
        }

        throw new FileNotFoundException();
    }

    /**
     * Prepare data from desire conditions
     *
     * @return $this
     * @throws ConditionNotAllowedException
     */
    protected function prepare()
    {
        if ($this->_isProcessed) {
            return $this;
        }

        if (count($this->_conditions) > 0) {
            $calculatedData = $this->processConditions();
            $this->_map = $this->objectToArray($calculatedData);

            $this->_conditions = [];
            $this->_node = '';
            $this->_isProcessed = true;
            return $this;
        }

        $this->_isProcessed = true;
        $this->_map = $this->objectToArray($this->getData());
        return $this;
    }

    /**
     * Our system will cache processed data and prevend multiple time processing. If
     * you want to reprocess this method can help you
     *
     * @return $this
     */
    public function reProcess()
    {
        $this->_isProcessed = false;
        return $this;
    }

    /**
     * Parse object to array
     *
     * @param object $obj
     * @return array|mixed
     */
    protected function objectToArray($obj)
    {
        if (!is_array($obj) && !is_object($obj)) {
            return $obj;
        }

        if (is_array($obj)) {
            return $obj;
        }

        if (is_object($obj)) {
            $obj = get_object_vars($obj);
        }

        return array_map([$this, 'objectToArray'], $obj);
    }

    /**
     * Check given value is multidimensional array
     *
     * @param array $arr
     * @return bool
     */
    protected function isMultiArray($arr)
    {
        if (!is_array($arr)) {
            return false;
        }

        rsort($arr);

        return isset($arr[0]) && is_array($arr[0]);
    }

    /**
     * Check given value is valid JSON
     *
     * @param string $value
     * @param bool $isReturnMap
     *
     * @return bool|array
     */
    public function isJson($value, $isReturnMap = false)
    {
        if (is_array($value) || is_object($value)) {
            return false;
        }

        $data = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        return $isReturnMap ? $data : true;
    }


    public function takeColumn($array)
    {
        return $this->selectColumn($this->exceptColumn($array));
    }

    /**
     * selecting specific column
     *
     * @param $array
     * @return array
     */
    protected function selectColumn($array)
    {
        $keys = $this->_select;

        if (count($keys) == 0) {
            return $array;
        }

        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * selecting specific column
     *
     * @param $array
     * @return array
     */
    protected function exceptColumn($array)
    {
        $keys = $this->_except;

        if (count($keys) == 0) {
            return $array;
        }

        return array_diff_key($array, array_flip((array) $keys));
    }


    /**
     * Prepare data for result
     *
     * @param mixed $data
     * @param bool $isObject
     * @return array|mixed
     */
    protected function prepareResult($data, $isObject)
    {
        $output = [];

        if (is_null($data) || is_scalar($data)) {
            return $data;
        }

        if ($this->isMultiArray($data)) {
            foreach ($data as $key => $val) {
                $val = $this->takeColumn($val);
                $output[$key] = $isObject ? (object) $val : $val;
            }
        } else {
            $output = json_decode(json_encode($this->takeColumn($data)), $isObject);
        }

        return $output;
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
     * Get data from nested array
     *
     * @param $map array
     * @param $node string
     * @return bool|array|mixed
     */
    protected function getFromNested($map, $node)
    {
        if (empty($node) || $node == '.') {
            return $map;
        }

        if ($node) {
            $terminate = false;
            $path = explode('.', $node);

            foreach ($path as $val) {
                if (!is_array($map)) return $map;

                if (!array_key_exists($val, $map)) {
                    $terminate = true;
                    break;
                }

                $map = &$map[$val];
            }

            if ($terminate) {
                return new ValueNotFound();
            }

            return $map;
        }

        return new ValueNotFound();
    }

    /**
     * get data from node path
     *
     * @return mixed
     */
    protected function getData()
    {
        return $this->getFromNested($this->_map, $this->_node);
    }

    /**
     * process AND and OR conditions
     *
     * @return array|string|object
     * @throws ConditionNotAllowedException
     */
    protected function processConditions()
    {
        $data = $this->getData();
        $conditions = $this->_conditions;
        $result = array_filter($data, function ($val) use ($conditions) {
            $res = false;
            foreach ($conditions as $cond) {
                $tmp = true;
                foreach ($cond as $rule) {
                    $function = self::$_rulesMap[$rule['condition']];
                    if (!is_callable($function)) {
                        if (!method_exists(Condition::class, $function)) {
                            throw new ConditionNotAllowedException("Exception: $function condition not allowed");
                        }

                        $function = [Condition::class, $function];
                    }

                    $value = $this->getFromNested($val, $rule['key']);
                    $return = $value instanceof ValueNotFound ? false :  call_user_func_array($function, [$value, $rule['value']]);
                    $tmp &= $return;
                }
                $res |= $tmp;
            }
            return $res;
        });

        return $result;
    }

    /**
     * make WHERE clause
     *
     * @param string $key
     * @param string $condition
     * @param mixed $value
     * @return $this
     */
    public function where($key, $condition = null, $value = null)
    {
        if (!is_null($condition) && is_null($value)) {
            $value = $condition;
            $condition = '=';
        }

        if (count($this->_conditions) < 1) {
            array_push($this->_conditions, []);
        }
        return $this->makeWhere($key, $condition, $value);
    }

    /**
     * make WHERE clause with OR
     *
     * @param string $key
     * @param string $condition
     * @param mixed $value
     * @return $this
     */
    public function orWhere($key = null, $condition = null, $value = null)
    {
        if (!is_null($condition) && is_null($value)) {
            $value = $condition;
            $condition = '=';
        }

        array_push($this->_conditions, []);

        return $this->makeWhere($key, $condition, $value);
    }

    /**
     * generator for AND and OR where
     *
     * @param string $key
     * @param string $condition
     * @param mixed $value
     * @return $this
     */
    protected function makeWhere($key, $condition = null, $value = null)
    {
        $current = end($this->_conditions);
        $index = key($this->_conditions);
        if (is_callable($key)) {
            $key($this);
            return $this;
        }

        array_push($current, [
            'key' => $key,
            'condition' => $condition,
            'value' => $value,
        ]);

        $this->_conditions[$index] = $current;

        return $this;
    }

    /**
     * make WHERE IN clause
     *
     * @param string $key
     * @param array $value
     * @return $this
     */
    public function whereIn($key = null, $value = [])
    {
        $this->where($key, 'in', $value);

        return $this;
    }

    /**
     * make WHERE NOT IN clause
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function whereNotIn($key = null, $value = [])
    {
        $this->where($key, 'notin', $value);
        return $this;
    }

    /**
     * make WHERE NULL clause
     *
     * @param string $key
     * @return $this
     */
    public function whereNull($key = null)
    {
        $this->where($key, 'null', 'null');
        return $this;
    }


    /**
     * make WHERE Boolean clause
     *
     * @param string $key
     * @return $this
     */
    public function whereBool($key = null, $value)
    {
        if (is_bool($value)) {
            $this->where($key, '==', $value);
        }
        return $this;
    }

    /**
     * make WHERE NOT NULL clause
     *
     * @param string $key
     * @return $this
     */
    public function whereNotNull($key = null)
    {
        $this->where($key, 'notnull', 'null');

        return $this;
    }

    /**
     * make WHERE START WITH clause
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function whereStartsWith($key, $value)
    {
        $this->where($key, 'startswith', $value);

        return $this;
    }

    /**
     * make WHERE ENDS WITH clause
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function whereEndsWith($key, $value)
    {
        $this->where($key, 'endswith', $value);

        return $this;
    }

    /**
     * make WHERE MATCH clause
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function whereMatch($key, $value)
    {
        $this->where($key, 'match', $value);

        return $this;
    }

    /**
     * make WHERE CONTAINS clause
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function whereContains($key, $value)
    {
        $this->where($key, 'contains', $value);

        return $this;
    }

    /**
     * make WHERE DATE clause
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function whereDate($key, $value)
    {
        $this->where($key, 'dates', $value);

        return $this;
    }

    /**
     * make WHERE month clause
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function whereMonth($key, $value)
    {
        $this->where($key, 'month', $value);

        return $this;
    }

    /**
     * make WHERE Year clause
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function whereYear($key, $value)
    {
        $this->where($key, 'year', $value);

        return $this;
    }

    /**
     * make macro for custom where clause
     *
     * @param string $name
     * @param callable $fn
     * @return bool
     */
    public static function macro($name, callable $fn)
    {
        if (!in_array($name, self::$_rulesMap)) {
            self::$_rulesMap[$name] = $fn;
            return true;
        }

        return false;
    }
}
