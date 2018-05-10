<?php

$rootDir = str_replace('\\', '/', dirname(__DIR__, 1)) . DIRECTORY_SEPARATOR;

require $rootDir . 'vendor/autoload.php';

use Nahid\JsonQ\Jsonq;


$result = '';
$json=new Jsonq($rootDir . 'data.json');
$json1 = $json->copy();
$result = $json->from('products');
echo '<pre>';
dump($json->sum('price'));
dump($json->copy()->collect([1,2,6])->avg());


/* ----------- avg method example ------------ */
/*
$json=new Jsonq($rootDir . 'data.json');
$avg = $json->from('products')->avg('price');

echo '<pre>';
dump($avg);

$json=new Jsonq();
$avg = $json->collect([1, 3, 2])->avg();

echo '<pre>';
dump($avg);*/


/* ----------- nth method example ---------- */
/*$json=new Jsonq($rootDir . 'data.json');
$data = $json->collect([1, 2, 3, 4, 5])->nth(-2);

echo '<pre>';
dump($data);