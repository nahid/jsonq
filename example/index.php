<?php
require 'vendor/autoload.php';

use Nahid\JsonQ\Jsonq;

$json=new Jsonq('composer.json');

$x = $json->node('license')->get();

var_dump($x);



// var_dump($json->node('products')->where('name', '=', 'Keyboard')->fetch());
