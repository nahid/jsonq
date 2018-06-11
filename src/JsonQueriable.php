<?php

namespace Nahid\JsonQ;

use Nahid\JsonQ\Exceptions\ConditionNotAllowedException;
use Nahid\JsonQ\Exceptions\FileNotFoundException;
use Nahid\JsonQ\Exceptions\InvalidJsonException;

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
        '==' => 'exactEqual',
        'seq' => 'exactEqual',
        '!=' => 'notEqual',
        'neq' => 'notEqual',
        '!==' => 'notExactEqual',
        'sneq' => 'notExactEqual',
        '>' => 'greater',
        'gt' => 'greater',
        '<' => 'less',
        'lt' => 'less',
        '>=' => 'greaterEqual',
        'gte' => 'greaterEqual',
        '<=' => 'lessEqual',
        'lte' => 'lessEqual',
        'in'    => 'in',
        'notin' => 'notIn',
        'null' => 'null',
        'notnull' => 'notNull',
        'startswith' => 'startsWith',
        'endswith' => 'endsWith',
        'match' => 'match',
        'contains' => 'contains',
        'dates' => 'dates',
        'month' => 'month',
        'year' => 'year',
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
     * @return array
     */
    protected function prepareResult($data, $isObject)
    {
        $output = [];
        if ($this->isMultiArray($data)) {
            foreach ($data as $key => $val) {
                $val = $this->takeColumn($val);
                $output[$key] = $isObject ? (object) $val : $val;
            }
        } else {
            $output = json_decode(json_encode($this->takeColumn($data)), !$isObject);
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
     * get data from node path
     *
     * @return mixed
     */
    protected function getData()
    {
        if (empty($this->_node) || $this->_node == '.') {
            return $this->_map;
        }

        if ($this->_node) {
            $terminate = false;
            $map = $this->_map;
            $path = $this->_node;

            foreach ($path as $val) {
                if (!isset($map[$val])) {
                    $terminate = true;
                    break;
                }

                $map = &$map[$val];
            }

            if ($terminate) {
                return false;
            }

            return $map;
        }

        return false;
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
                    $call_func = [];
                    if (is_callable(self::$_rulesMap[$rule['condition']])) {
                        $func = self::$_rulesMap[$rule['condition']];
                        $call_func = $func;
                    } else {
                        $func = 'cond' . ucfirst(self::$_rulesMap[$rule['condition']]);
                        $call_func[] = $this;
                        $call_func[] = $func;
                    }
                    if (is_callable($func) || method_exists($this, $func) ) {
                        if (isset($val[$rule['key']])) {
                            $return = call_user_func_array($call_func, [$val[$rule['key']], $rule['value']]);
                        } else {
                            $return = false;
                        }
                        $tmp &= $return;
                    } else {
                        throw new ConditionNotAllowedException('Exception: ' . $func . ' condition not allowed');
                    }
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
        $this->where($key, 'null', null);
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
        $this->where($key, 'notnull', null);

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

    // condition methods

    /**
     * make Equal condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condEqual($val, $payable)
    {
        return $val == $payable;
    }

    /**
     * make Exact Equal condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condExactEqual($val, $payable)
    {
        return $val === $payable;
    }

    /**
     * make Not Equal condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condNotEqual($val, $payable)
    {
        return $val != $payable;
    }

    /**
     * make Not Exact Equal condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condNotExactEqual($val, $payable)
    {
        return $val !== $payable;
    }

    /**
     * make Greater Than condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condGreater($val, $payable)
    {
        return $val > $payable;
    }

    /**
     * make Less Than condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condLess($val, $payable)
    {
        return $val < $payable;
    }

    /**
     * make Greater Equal condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condGreaterEqual($val, $payable)
    {
        return $val >= $payable;
    }

    /**
     * make Less Equal condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condLessEqual($val, $payable)
    {
        return $val <= $payable;
    }

    /**
     * make In condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condIn($val, $payable)
    {
        return (is_array($payable) && in_array($val, $payable));
    }

    /**
     * make Not In condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condNotIn($val, $payable)
    {
        return (is_array($val) && !in_array($val, $payable));
    }

    /**
     * make Null condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condNull($val, $payable)
    {
        return (is_null($val) || $val == $payable);
    }

    /**
     * make Not Null condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condNotNull($val, $payable)
    {
        return (!is_null($val) && $val !== $payable);
    }

    /**
     * make Starts With condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condStartsWith($val, $payable)
    {
        if (preg_match("/^$payable/", $val)) {
            return true;
        }

        return false;
    }

    /**
     * make Ends With condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condEndsWith($val, $payable)
    {
        if (preg_match("/$payable$/", $val)) {
            return true;
        }

        return false;
    }

    /**
     * make Match condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condMatch($val, $payable)
    {
        $payable = rtrim($payable, '$/');
        $payable = ltrim($payable, '/^');

        $pattern = '/^'.$payable.'$/';
        if (preg_match($pattern, $val)) {
            return true;
        }

        return false;
    }

    /**
     * make Contains condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condContains($val, $payable)
    {
        return (strpos($val, $payable) !== false);
    }

    /**
     * make date condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condDates($val, $payable)
    {
        $date = date('Y-m-d', strtotime($val));
        return $date == $payable;
    }

    /**
     * make month condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condMonth($val, $payable)
    {
        $month = date('m', strtotime($val));
        return $month == $payable;
    }

    /**
     * make year condition
     *
     * @param string $val
     * @param mixed $payable
     * @return bool
     */
    protected function condYear($val, $payable)
    {
        $year = date('Y', strtotime($val));
        return $year == $payable;
    }
}
