<?php

$rootDir = str_replace('\\', '/', dirname(__DIR__, 1)) . DIRECTORY_SEPARATOR;

require $rootDir . 'vendor/autoload.php';

use Nahid\JsonQ\Jsonq;

$json=new Jsonq();

$json->import($rootDir . 'data.json');

$result = $json->from('products')
    ->where('cat', '=', 1)
    ->prepare()
    ->get();


echo '<pre>';
dump($result);
