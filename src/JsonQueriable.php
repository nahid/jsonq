<?php

namespace Nahid\JsonQ;

use Nahid\JsonQ\Exceptions\ConditionNotAllowedException;
use Nahid\JsonQ\Exceptions\FileNotFoundException;

trait JsonQueriable
{
    /**
     * store node path
     * @var string
     */
    protected $_node = '';
    /**
     * contain prepared data for process
     * @var mixed
     */
    protected $_map;


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


    protected $_isProcessed = false;

    /**
     * map all conditions with methods
     * @var array
     */
    protected $_rulesMap = [
        '=' => 'equal',
        '==' => 'exactEqual',
        '!=' => 'notEqual',
        '!==' => 'notExactEqual',
        '>' => 'greater',
        '<' => 'less',
        '>=' => 'greaterEqual',
        '<=' => 'lessEqual',
        'in'    => 'in',
        'notin' => 'notIn',
        'null' => 'null',
        'notnull' => 'notNull',
        'startswith' => 'startsWith',
        'endswith' => 'endsWith',
        'match' => 'match',
        'contains' => 'contains',
        'macro' => 'macro',
    ];


    /**
     * import data from file
     *
     * @param $json_file string
     * @return bool
     * @throws FileNotFoundException
     */
    public function import($json_file = null)
    {
        if (!is_null($json_file)) {
            if (file_exists($json_file)) {
                $this->_map = $this->getDataFromFile($json_file);
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

        if (is_array($obj)) {
            return $obj;
        }

        if (is_object($obj)) {
            $obj = get_object_vars($obj);
        }

        return array_map([$this, 'objectToArray'], $obj);
    }



    /**
     * check given value is multidimensional array
     *
     * @param $arr array
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
     * check given value is valid JSON
     * @param $value string
     * @param $return_map bool
     * @return bool|array|string
     */
    public function isJson($value, $return_map = false)
    {
        $data = json_decode($value, true);

        return (json_last_error() == JSON_ERROR_NONE) ? ($return_map ? $data : true) : json_last_error_msg();
    }
    protected function prepareResult($data, $object)
    {
        $output = [];
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if ($object) {
                    $output[$key] = (object) $val;
                } else {
                    $output[$key] = $val;
                }
            }
        } else {
            if ($object) {
                $output = json_decode(json_encode($data));
            } else {
                $output = json_decode(json_encode($data), true);
            }
        }

        return $output;
    }

    /**
     * read JSON data from file
     *
     * @param $file string
     * @param $type string
     * @return bool|string|array
     * @throws FileNotFoundException
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

            return $this->isJson($data, true);
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
                    $func = 'cond' . ucfirst($this->_rulesMap[$rule['condition']]);
                    if (method_exists($this, $func)) {
                        $return = call_user_func_array([$this, $func], [$val[$rule['key']], $rule['value']]);
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
     * @param $key string
     * @param $condition string
     * @param $value mixed
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
     * @param $key string
     * @param $condition string
     * @param $value mixed
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
     * @param $key string
     * @param $condition string
     * @param $value mixed
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
     * @param $key string
     * @param $value array
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
     * @param $key string
     * @param $value mixed
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
     * @param $key string
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
     * @param $key string
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
     * @param $key string
     * @param $value string
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
     * @param $key string
     * @param $value string
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
     * @param $key string
     * @param $value string
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
     * @param $key string
     * @param $value string
     * @return $this
     */
    public function whereContains($key, $value)
    {
        $this->where($key, 'contains', $value);

        return $this;
    }

    /**
     * make macro for custom where clause
     *
     * @param $key string
     * @param $fn callable
     * @return $this
     */
    public function macro($key, callable $fn)
    {
        $this->where($key, 'macro', $fn);

        return $this;
    }



    // condition methods

    /**
     * make Equal condition
     *
     * @param $key string
     * @param $val mixed
     * @return bool
     */
    protected function condEqual($key, $val)
    {
        if ($key == $val) {
            return true;
        }
        return false;
    }

    /**
     * make Exact Equal condition
     *
     * @param $key string
     * @param $val mixed
     * @return bool
     */
    protected function condExactEqual($key, $val)
    {
        if ($key === $val) {
            return true;
        }
        return false;
    }

    /**
     * make Not Equal condition
     *
     * @param $key string
     * @param $val mixed
     * @return bool
     */
    protected function condNotEqual($key, $val)
    {
        if ($key != $val) {
            return true;
        }
        return false;
    }

    /**
     * make Not Exact Equal condition
     *
     * @param $key string
     * @param $val mixed
     * @return bool
     */
    protected function condNotExactEqual($key, $val)
    {
        if ($key !== $val) {
            return true;
        }
        return false;
    }

    /**
     * make Greater Than condition
     *
     * @param $key string
     * @param $val mixed
     * @return bool
     */
    protected function condGreater($key, $val)
    {
        if ($key > $val) {
            return true;
        }
        return false;
    }

    /**
     * make Less Than condition
     *
     * @param $key string
     * @param $val mixed
     * @return bool
     */
    protected function condLess($key, $val)
    {
        if ($key < $val) {
            return true;
        }
        return false;
    }

    /**
     * make Greater Equal condition
     *
     * @param $key string
     * @param $val mixed
     * @return bool
     */
    protected function condGreaterEqual($key, $val)
    {
        if ($key >= $val) {
            return true;
        }
        return false;
    }

    /**
     * make Less Equal condition
     *
     * @param $key string
     * @param $val mixed
     * @return bool
     */
    protected function condLessEqual($key, $val)
    {
        if ($key <= $val) {
            return true;
        }
        return false;
    }

    /**
     * make In condition
     *
     * @param $key string
     * @param $val mixed
     * @return bool
     */
    protected function condIn($key, $val)
    {
        if (is_array($val)) {
            if (in_array($key, $val)) {
                return true;
            }
        }
        return false;
    }

    /**
     * make Not In condition
     *
     * @param $key string
     * @param $val mixed
     * @return bool
     */
    protected function condNotIn($key, $val)
    {
        if (is_array($val)) {
            if (!in_array($key, $val)) {
                return true;
            }
        }
        return false;
    }

    /**
     * make Null condition
     *
     * @param $key string
     * @param $val mixed
     * @return bool
     */
    protected function condNull($key, $val)
    {
        if (is_null($key) || $key == $val) {
            return true;
        }
        return false;
    }

    /**
     * make Not Null condition
     *
     * @param $key string
     * @param $val mixed
     * @return bool
     */
    protected function condNotNull($key, $val)
    {
        if (!is_null($key) && $key !== $val) {
            return true;
        }
        return false;
    }

    /**
     * make Starts With condition
     *
     * @param $key string
     * @param $val mixed
     * @return bool
     */
    protected function condStartsWith($key, $val)
    {
        $pattern = '/^'.$val.'/';
        if (preg_match($pattern, $key)) {
            return true;
        }

        return false;
    }

    /**
     * make Match condition
     *
     * @param $key string
     * @param $val mixed
     * @return bool
     */
    protected function condMatch($key, $val)
    {
        $val = rtrim($val, '$/');
        $val = ltrim($val, '/^');

        $pattern = '/^'.$val.'$/';
        if (preg_match($pattern, $key)) {
            return true;
        }

        return false;
    }

    /**
     * make Contains condition
     *
     * @param $key string
     * @param $val mixed
     * @return bool
     */
    protected function condContains($key, $val)
    {
        if (strpos($key, $val) !== false) {
            return true;
        }

        return false;
    }

    /**
     * make Macro condition
     *
     * @param $key string
     * @param $fn callable
     * @return bool
     */
    protected function condMacro($key, callable $fn)
    {
        if (is_callable($fn)) {
            return $fn($key);
        }

        return false;
    }
}
