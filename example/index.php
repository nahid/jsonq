<?php

$rootDir = str_replace('\\', '/', dirname(__DIR__, 1)) . DIRECTORY_SEPARATOR;

require $rootDir . 'vendor/autoload.php';

use Nahid\JsonQ\Jsonq;


$result = '';
$json=new Jsonq($rootDir . 'data.json');
$json1 = $json->copy();
$result = $json->from('users')
    ->prepare()
    ->groupBy('locations');


echo '<pre>';
dump($json->get());


/* ----------- avg method example ------------ */

$json=new Jsonq($rootDir . 'data.json');
$avg = $json->from('products')->prepare()->avg('price');

echo '<pre>';
dump($avg);

$json=new Jsonq();
$avg = $json->collect([1, 3, 2])->prepare()->avg();

echo '<pre>';
dump($avg);