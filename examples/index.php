<?php

require '../vendor/autoload.php';

use Nahid\JsonQ\Jsonq;


$result = '';
$json=new Jsonq('data.json');
$result = $json->from('users')
    ->where('cat', '=', 2)
    ->sum('price');
dump($result);