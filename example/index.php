<?php
require '../vendor/autoload.php';

use Nahid\JsonQ\Jsonq;

$json=new Jsonq();
$json->import('../data.json');


$result = $json->from('products')
    ->where('cat', '=', 1)
    ->fetch()
    ->sortAs('price', 'desc')
    ->first();
            

echo '<pre>';
dump($result);

