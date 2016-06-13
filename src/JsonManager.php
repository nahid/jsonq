<?php

namespace Nahid\JsonQ;

class JsonManager
{
	protected $_node;
	protected $_map;
	protected $_path = '';

	public function __construct($path=null)
	{
		if(!empty($path) && !is_null($path)) {
			$this->_path = $path.'/';
		}
	}

	public function import($jsonFile=null)
	{
		if(!is_null($jsonFile)) {
			$this->_db = $jsonFile;
			$this->_path .= $jsonFile;

            if(!file_exists($this->_path)) {
                return false;
            }

            $this->_map = $this->getDataFromFile($this->_path);
            //var_dump($this->_map);
            return true;
		}
	}


	public function setStoragePath($path)
	{
		$this->_path = $path.'/';
	}

	protected function isMultiArray( $arr ) {
	    rsort( $arr );
	    return isset( $arr[0] ) && is_array( $arr[0] );
	}

	public function isJson($string, $return_map = false)
	{
		 $data = json_decode($string, true);
	     return (json_last_error() == JSON_ERROR_NONE) ? ($return_map ? $data : true) : json_last_error_msg();
	}


	protected function getDataFromFile($file, $type = 'application/json')
	{
		if(!$file) {
			return false;
		}
		if(file_exists($file)) {
			$opts = [
				'http'=>[
					'header' => 'Content-Type: '.$type.'; charset=utf-8'
				]
			];

			$context = stream_context_create($opts);

			$data=file_get_contents($file, 0, $context);

			return $this->isJson($data, true);
		}
	}


    protected function getData()
	{
		if($this->_node) {
			$terminate=false;
			$map = $this->_map;
			$path=$this->_node;

			foreach($path as $val){

				if(!isset($map[$val])){
					$terminate=true;
					break;
				}

				$map = &$map[$val];
			}

			if($terminate) return false;

			$this->_calculatedData  = $this->_data = $map;

			return $map;
		}
		return false;
	}

	protected function runFilter($data, $key, $condition, $value)
	{
	    $func ='where'. ucfirst($this->_conditions[$condition]);
	    return $this->$func($data, $key, $value);
	}

	protected function makeWhere($rule, $key=null, $condition=null, $value=null)
	{
		$data = $this->getData();
		$calculatedData = $this->runFilter($data, $key, $condition, $value);
		if(!is_null($this->_calculatedData)) {
			if($rule=='and')
				$calculatedData = array_intersect(array_keys($this->_calculatedData), array_keys($calculatedData));

			if($rule=='or')
				$calculatedData = array_merge(array_keys($this->_calculatedData), array_keys($calculatedData));

			$this->_calculatedData='';

			foreach ($calculatedData as $value) {
				$this->_calculatedData[$value]= $data[$value];
			}
			return true;
		}
		$this->_calculatedData = $calculatedData;
		return true;
	}



	public function isStrStartWith($string, $like)
	{
		$pattern = '/^'. $like. '/';
		if(preg_match($pattern, $string)) {
			return true;
		}

		return false;
	}

	public function makeUniqueName($prefix='jsonq', $hash=false)
	{
		$name = uniqid();
		if($hash) {
			return $prefix.md5($name);
		}
		return $prefix.$name;
	}

}
