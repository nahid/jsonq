<?php

namespace Nahid\JsonQ;

class JsonManager
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

    public function __construct($path = null)
    {
        if (!empty($path) && !is_null($path)) {
            $this->_path = $path.'/';
        }
    }

    public function import($jsonFile = null)
    {
        if (!is_null($jsonFile)) {
            $this->_path .= $jsonFile;

            if (!file_exists($this->_path)) {
                return false;
            }

            $this->_map = $this->getDataFromFile($this->_path);
            return true;
        }
    }

    public function setStoragePath($path)
    {
        $this->_path = $path.'/';
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
        if (!$file) {
            return false;
        }
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

    public function makeUniqueName($prefix = 'jsonq', $hash = false)
    {
        $name = uniqid();
        if ($hash) {
            return $prefix.md5($name);
        }

        return $prefix.$name;
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

    protected function condEqual($key, $val)
    {
        if ($key == $val) {
            return true;
        }
    }
    protected function condNotEqual($key, $val)
    {
        if ($key != $val) {
            return true;
        }
    }
    protected function condGreater($key, $val)
    {
        if ($key > $val) {
            return true;
        }
    }
    protected function condLess($key, $val)
    {
        if ($key < $val) {
            return true;
        }
    }
    protected function condGreaterEqual($key, $val)
    {
        if ($key >= $val) {
            return true;
        }
    }
    protected function condLessEqual($key, $val)
    {
        if ($key <= $val) {
            return true;
        }
    }

    protected function condIn($key, $val)
    {
        if (is_array($val)) {
            if (in_array($key, $val)) {
                return true;
            }
        }
    }

    protected function condNotIn($key, $val)
    {
        if (is_array($val)) {
            if (!in_array($key, $val)) {
                return true;
            }
        }
    }

    protected function condNull($key, $val)
    {
        if (is_null($key) || $key == $val) {
            return true;
        }
    }

    protected function condNotNull($key, $val)
    {
        if (!is_null($key) && $key !== $val) {
            return true;
        }
    }
}
