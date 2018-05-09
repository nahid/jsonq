<?php

$rootDir = str_replace('\\', '/', dirname(__DIR__, 1)) . DIRECTORY_SEPARATOR;

require $rootDir . 'vendor/autoload.php';

use Nahid\JsonQ\Jsonq;


$result = '';
    $json=new Jsonq($rootDir . 'data.json');
    $json1 = $json->copy();
    $result = $json->from('products')
        ->where('id', '=', 7)
        ->prepare();


echo '<pre>';
dump($json->exists());
