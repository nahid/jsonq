<?php

namespace Nahid\JsonQ;

use Nahid\JsonQ\JsonManager;

class Jsonq extends JsonManager
{
	protected $_file;
	protected $_node='';
	protected $_data=array();


	protected $_calculatedData = null;

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

	public function get()
	{
		$calculatedData = $this->processConditions();

		$resultingData = [];

		foreach ($calculatedData as $data) {
			$resultingData[]	= $data;
		}

		return $resultingData;



		/*if(is_null($this->_calculatedData)) {
			return $this->getData();
		}

		return $this->_calculatedData;*/
	}

	public function fetch()
	{
		return $this->get();
	}

	public function first()
	{
		if(is_null($this->_calculatedData)) {
			$data = $this->getData();
			if(is_array($data)) {
				return json_decode(json_encode(reset($data)));
			}

			return $data;

		}

		return json_decode(json_encode(reset($this->_calculatedData)));
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



	protected function whereGreater($data, $key, $value)
	{
		return array_filter($data, function($var) use($key, $value){
			if(isset($var[$key]))
			if($var[$key]>$value){
				return $var;
			}
		});
	}

	protected function whereLess($data, $key, $value)
	{
		return array_filter($data, function($var) use($key, $value){
			if(isset($var[$key]))
			if($var[$key]<$value){
				return $var;
			}
		});
	}

	protected function whereEqual($data, $key, $value)
	{
		return array_filter($data, function($var) use($key, $value){
			if(isset($var[$key]))
			if($var[$key]==$value){
				return $var;
			}
		});
	}

	protected function whereGreaterequal($data, $key, $value)
	{
		return array_filter($data, function($var) use($key, $value){
			if(isset($var[$key]))
			if($var[$key]>=$value){
				return $var;
			}
		});
	}
	protected function whereLessequal($data, $key, $value)
	{
		return array_filter($data, function($var) use($key, $value){
			if(isset($var[$key]))
			if($var[$key]<=$value){
				return $var;
			}
		});
	}

	protected function whereNotequal($data, $key, $value)
	{
		return array_filter($data, function($var) use($key, $value){
			if(isset($var[$key]))
			if($var[$key]!=$value){
				return $var;
			}
		});
	}
}
