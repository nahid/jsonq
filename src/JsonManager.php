<?php

namespace Nahid\JsonQ;

class JsonManager
{
	protected $_node;
	protected $_map;
	protected $_path = '';


	public function import($jsonFile=null)
	{
		if(!is_null($jsonFile)) {
			$this->_db = $jsonFile;
			$this->_path = $jsonFile;

            if(!file_exists($this->_path)) {
                return false;
            }
            
            $this->_map = $this->getDataFromFile($this->_path);
            //var_dump($this->_map);
            return true;
		}
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
