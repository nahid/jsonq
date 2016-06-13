<?php
require '../vendor/autoload.php';

use Nahid\JsonQ\Jsonq;

$json=new Jsonq();
$json->import('../data.json');


$result = $json->node('items')->where('id', '>', 4)->where('price', '=', 1500)->get();

var_dump($result);



// var_dump($json->node('products')->where('name', '=', 'Keyboard')->fetch());
