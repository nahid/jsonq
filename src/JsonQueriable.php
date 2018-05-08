<?php

namespace Nahid\JsonQ;

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
     * store  file path in string
     * @var string
     */
    protected $_path = '';

    /**
     * map all conditions with methods
     * @var array
     */
    protected $_rulesMap = [
        '=' => 'equal',
        '!=' => 'notEqual',
        '>' => 'greater',
        '<' => 'less',
        '>=' => 'greaterEqual',
        '<=' => 'lessEqual',
        'in'    => 'in',
        'notin' => 'notIn',
        'null' => 'null',
        'notnull' => 'notNull',
    ];

    /**
     * Stores all conditions.
     *
     * @var array
     */
    protected $_conditions = [];

    /**
     * import data from file
     *
     * @param $jsonFile string
     * @return bool
     * @throws FileNotFoundException
     */
    public function import($jsonFile = null)
    {
        if (!is_null($jsonFile)) {
            $this->_path = $jsonFile;

            if (file_exists($this->_path)) {
                $this->_map = $this->getDataFromFile($this->_path);
                return true;
            }
        }

        throw new FileNotFoundException();
    }


    /**
     * check given value is multidimensional array
     *
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

            $this->_calculatedData = $map;

            return $map;
        }

        return false;
    }

    /**
     * check the given string is start with the given value
     * @param $string string
     * @param $like string
     * @return bool
     */
    public function isStrStartWith($string, $like)
    {
        $pattern = '/^'.$like.'/';
        if (preg_match($pattern, $string)) {
            return true;
        }

        return false;
    }


    /**
     * process AND and OR conditions
     *
     * @return array|string
     */
    protected function processConditions()
    {
        $data = $this->getData();
        $conds = $this->_conditions;
        $result = array_filter($data, function ($val) use ($conds) {
            $res = false;
            foreach ($conds as $cond) {
                $tmp = true;
                foreach ($cond as $rule) {
                    $func = 'cond' . ucfirst($this->_rulesMap[$rule['condition']]);
                    if (method_exists($this, $func)) {
                        $return = call_user_func_array([$this, $func], [$val[$rule['key']], $rule['value']]);
                        $tmp &= $return;
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
        if (count($this->_conditions) < 1) {
            $this->_conditions[] = [];
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
        if (count($this->_conditions) < 1) {
            $this->_conditions[] = [];
        } else {
            array_push($this->_conditions, []);
        }

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
}
