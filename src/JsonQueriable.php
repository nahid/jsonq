<?php

namespace Nahid\JsonQ;

use Nahid\JsonQ\Exceptions\FileNotFoundException;

trait JsonQueriable
{
    protected $_node = '';
    protected $_map;
    protected $_path = '';
    protected $_conds = [
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
     * Stores where conditions.
     *
     * @var array
     */
    protected $_andConditions = [];

    /**
     * Stores orWhere conditions.
     *
     * @var array
     */
    protected $_orConditions = [];

    public function import($jsonFile = null)
    {
        if (!is_null($jsonFile)) {
            $this->_path .= $jsonFile;

            if (file_exists($this->_path)) {
                $this->_map = $this->getDataFromFile($this->_path);
                return true;
            }
        }

        throw new FileNotFoundException();
    }


    protected function isMultiArray($arr)
    {
        if (!is_array($arr)) {
            return false;
        }

        rsort($arr);

        return isset($arr[0]) && is_array($arr[0]);
    }

    public function isJson($string, $return_map = false)
    {
        $data = json_decode($string, true);

        return (json_last_error() == JSON_ERROR_NONE) ? ($return_map ? $data : true) : json_last_error_msg();
    }

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

            $this->_calculatedData = $this->_data = $map;

            return $map;
        }

        return false;
    }

    public function isStrStartWith($string, $like)
    {
        $pattern = '/^'.$like.'/';
        if (preg_match($pattern, $string)) {
            return true;
        }

        return false;
    }


    protected function processConditions()
    {
        $data = $this->getData();
        if (!$data) {
            return null;
        }

        if (is_string($data)) {
            return $data;
        }
        if (!$this->isMultiArray($data)) {
            return $data;
        }
        $andData = $this->fetchAndData();
        $orData = $this->fetchOrData();

        $newData = array_replace($andData, $orData);

        return $newData;
    }

    protected function fetchAndData()
    {
        $data = $this->getData();
        $conditions = $this->_andConditions;

        $calculatedData = [];

        foreach ($data as $id => $record) {
            if ($this->filterByAndConditions($record, $conditions)) {
                $calculatedData[$id] = $record;
            }
        }

        return $calculatedData;
    }

    protected function fetchOrData()
    {
        $data = $this->getData();
        $conditions = $this->_orConditions;

        $calculatedData = [];

        foreach ($data as $id => $record) {
            if ($this->filterByOrConditions($record, $conditions)) {
                $calculatedData[$id] = $record;
            }
        }

        return $calculatedData;
    }

    protected function filterByAndConditions($record, $conditions)
    {
        $return = false;
        foreach ($conditions as $rule) {
            $func = 'cond'.ucfirst($this->_conds[$rule['condition']]);
            if (method_exists($this, $func)) {
                if (call_user_func_array([$this,  $func], [$record[$rule['key']], $rule['value']])) {
                    $return = true;
                } else {
                    return false;
                }
            }
        }

        return $return;
    }

    protected function filterByOrConditions($record, $conditions)
    {
        $return = false;
        foreach ($conditions as $rule) {
            $func = 'cond'.ucfirst($this->_conds[$rule['condition']]);
            if (method_exists($this, $func)) {
                if (call_user_func_array([$this,  $func], [$record[$rule['key']], $rule['value']])) {
                    return true;
                }
            }
        }

        return $return;
    }

    public function where($key = null, $condition = null, $value = null)
    {
        //$this->makeWhere('and', $key, $condition, $value);
        $this->_andConditions [] = [
            'key' => $key,
            'condition' => $condition,
            'value' => $value,
        ];

        return $this;
    }

    public function orWhere($key = null, $condition = null, $value = null)
    {
        //$this->makeWhere('or', $key, $condition, $value);
        $this->_orConditions [] = [
            'key' => $key,
            'condition' => $condition,
            'value' => $value,
        ];

        return $this;
    }


    public function whereIn($key = null, $value = [])
    {
        //$this->makeWhere('or', $key, $condition, $value);
        $this->_andConditions [] = [
            'key' => $key,
            'condition' => 'in',
            'value' => $value,
        ];

        return $this;
    }


    public function whereNotIn($key = null, $value = [])
    {
        //$this->makeWhere('or', $key, $condition, $value);
        $this->_andConditions [] = [
            'key' => $key,
            'condition' => 'notin',
            'value' => $value,
        ];

        return $this;
    }


    public function whereNull($key = null)
    {
        //$this->makeWhere('or', $key, $condition, $value);
        $this->_andConditions [] = [
            'key' => $key,
            'condition' => 'null',
            'value' => null,
        ];

        return $this;
    }

    public function whereNotNull($key = null)
    {
        //$this->makeWhere('or', $key, $condition, $value);
        $this->_andConditions [] = [
            'key' => $key,
            'condition' => 'notnull',
            'value' => null,
        ];

        return $this;
    }

    protected function condEqual($key, $val)
    {
        if ($key == $val) {
            return true;
        }
        return false;
    }
    protected function condNotEqual($key, $val)
    {
        if ($key != $val) {
            return true;
        }
        return false;
    }
    protected function condGreater($key, $val)
    {
        if ($key > $val) {
            return true;
        }
        return false;
    }
    protected function condLess($key, $val)
    {
        if ($key < $val) {
            return true;
        }
        return false;
    }
    protected function condGreaterEqual($key, $val)
    {
        if ($key >= $val) {
            return true;
        }
        return false;
    }
    protected function condLessEqual($key, $val)
    {
        if ($key <= $val) {
            return true;
        }
        return false;
    }

    protected function condIn($key, $val)
    {
        if (is_array($val)) {
            if (in_array($key, $val)) {
                return true;
            }
        }
        return false;
    }

    protected function condNotIn($key, $val)
    {
        if (is_array($val)) {
            if (!in_array($key, $val)) {
                return true;
            }
        }
        return false;
    }

    protected function condNull($key, $val)
    {
        if (is_null($key) || $key == $val) {
            return true;
        }
        return false;
    }

    protected function condNotNull($key, $val)
    {
        if (!is_null($key) && $key !== $val) {
            return true;
        }
        return false;
    }
}
