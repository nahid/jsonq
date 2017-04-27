<?php
require '../vendor/autoload.php';

use Nahid\JsonQ\Jsonq;

$json=new Jsonq();
$json->import('../data.json');


$result = $json
			->node('items')
			->where('id', '=', 3)
			->where('price', '=', 1200)
			->get();

var_dump($result);



// var_dump($json->node('products')->where('name', '=', 'Keyboard')->fetch());
