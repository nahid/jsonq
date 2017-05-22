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

	public function node($node=null)
	{
		if(is_null($node) || $node=='') return false;
		if ($node == ':') {
			$this->_node = $node;
			return $this;
		}

		$this->_node=explode(':', $node);
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

	public function get($object = true)
	{
		//if (count(@$_andConditions)>0 or count(@$_orConditions)>0) {
			$calculatedData = $this->processConditions();

			if (is_string($calculatedData)) {
				return $calculatedData;
			}

			if (!$this->isMultiArray($calculatedData)) {
				return $calculatedData;
			}
			$resultingData = [];

			foreach ($calculatedData as $data) {
				if ($object) {
					$resultingData[]	= (object) $data;
				} else {
					$resultingData[]	= $data;
				}
			}

			unset($this->_andConditions);
			unset($this->_orConditions);
			$this->_node = '';
			$this->_andConditions = [];
			$this->_orConditions = [];

			return $resultingData;
		//}

		//return $this->getData();
		

	}

	public function fetch($object = true)
	{
		return $this->get($object);
	}


	public function first($object = true)
	{
		$data = $this->get(false);
		if (count($data>0)) {
			if ($object) {
				return json_decode(json_encode(reset($data)));
			}

			return json_decode(json_encode(reset($data)), true);
			
		}

		return null;
		
	}

	public function then($node)
	{
		$this->_map = $this->first(false);

		$this->node($node);
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



    /*
    getNodeValue()

    This method helps to you to find or get specific node value.

    @param 		: 	string $node // ':' colon separeted string

    @return 	: 	string/false
    */

	public function delete()
	{
		$json='';
		$node=$this->_node;

		$data = &$this->_data;
	    $finalKey = array_pop($node);
	    foreach ($node as $key) {
	        $data = &$data[$key];
	    }

	    if(isset($data[$finalKey])){
	    	unset($data[$finalKey]);
	    }else{
	    	return false;
	    }


		$json=json_encode($this->_data);

	    if(file_put_contents($this->_file, $json)){
	    	return $json;
	    }

	    return false;

	}

}
