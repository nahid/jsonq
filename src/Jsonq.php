<?php

namespace Nahid\JsonQ;

use Nahid\JsonQ\JsonManager;

class Jsonq extends JsonManager
{

	protected $_file;
	protected $_node='';
	protected $_data=array();


	protected $_conditions = [
		'>'=>'greater',
		'<'=>'less',
		'='=>'equal',
		'!='=>'notequal',
		'>='=>'greaterequal',
		'<='=>'lessequal',
		];

	/*
		this constructor set main json file path
		otherwise create it and read file contents
		and decode as an array and store it in $this->_data
	*/
	function __construct($jsonFile=null)
	{
        $path = pathinfo($jsonFile);

        if(!isset($path['extension']) && !is_null($jsonFile)) {
            parent::__construct($jsonFile);
        }

        if(!is_null($jsonFile) && isset($path['extension'])) {
            $this->import($jsonFile);
    		$this->_file = $this->_path;
        }
        
    }


    public function from($node=null)
	{
		if(is_null($node) || $node=='') return false;
		if ($node == '.') {
			$this->_node = $node;
			return $this;
		}

		$this->_node=explode('.', $node);
		return $this;
	}

	public function where($key=null, $condition=null, $value=null)
	{
		//$this->makeWhere('and', $key, $condition, $value);
		$this->_andConditions [] = [
			'key'	=>	$key,
			'condition'	=> $condition,
			'value'	=>	$value
		];

		return $this;
	}


	public function orWhere($key=null, $condition=null, $value=null)
	{
		//$this->makeWhere('or', $key, $condition, $value);
		$this->_orConditions [] = [
			'key'	=>	$key,
			'condition'	=> $condition,
			'value'	=>	$value
		];

		return $this;
	}

	public function fetch()
	{
		if (count($this->_andConditions)>0 or count($this->_orConditions)>0) {
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
                $resultingData[]	= (object) $data;
            } else {
                $resultingData[]	= $data;
            }
        }
        return $resultingData;
	}

    public function count()
    {
        return count($this->_map);
	}

    public function sum($property)
    {
        $sum = 0;
        foreach ($this->_map as $key => $val) {
            if(isset($val[$property])) {
                if (is_numeric($val[$property])) {
                    $sum += $val[$property];
                }
            }
        }

        return $sum;
	}

    public function max($property)
    {
        $max = max(array_column($this->_map, $property));

        return $max;
	}

	public function min($property)
    {
        $max = min(array_column($this->_map, $property));

        return $max;
	}


	public function first($object = true)
	{
		$data = $this->_map;
		if (count($data>0)) {
			if ($object) {
				return json_decode(json_encode(reset($data)));
			}

			return json_decode(json_encode(reset($data)), true);
			
		}

		return null;
		
	}

    public function sortAs($property, $order = 'asc')
    {
        if (!is_array($this->_map)) {
            return $this;
        }

        usort($this->_map, function($a, $b) use ($property, $order) {
            $val1 = $a[$property];
            $val2 = $b[$property];
            if (is_string($val1)) {
                $val1 = strtolower($val1);
            }

            if (is_string($val2)) {
                $val2 = strtolower($val2);
            }

            if($a[$property] == $b[$property]) {
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
        return $this->from($path)->fetch()->get();
	}

	public function then($node)
	{
		$this->_map = $this->fetch()->first(false);

		$this->from($node);
		return $this;
	}

	public function collect($data)
	{
		$this->_map = $this->objectToArray($data);
		return $this;
	}

	public function objectToArray($obj) {
        if(!is_array($obj) && !is_object($obj)) return $obj;

		if(is_object($obj)) $obj = get_object_vars($obj);

        return array_map([$this,'objectToArray'], $obj);
    }


}
