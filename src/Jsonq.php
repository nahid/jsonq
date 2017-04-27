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
		$calculatedData = $this->processConditions();

		$resultingData = [];

		foreach ($calculatedData as $data) {
			if ($object) {
				$resultingData[]	= (object) $data;
			} else {
				$resultingData[]	= $data;
			}
		}

		return $resultingData;

	}

	public function fetch($object = true)
	{
		return $this->get($object);
	}


	public function first()
	{
		$data = $this->get(false);
		if (count($data>0)) {
			return json_decode(json_encode(reset($data)));
		}

		return null;
		
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
