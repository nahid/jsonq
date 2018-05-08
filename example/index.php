<?php

$rootDir = str_replace('\\', '/', dirname(__DIR__, 1)) . DIRECTORY_SEPARATOR;

require $rootDir . 'vendor/autoload.php';

use Nahid\JsonQ\Jsonq;


$result = '';
    $json=new Jsonq($rootDir . 'data.json');
    $result = $json->from('products')
        ->where('id', '=', 2)
        ->orWhere(function($q) {
            $q->where('city', '=', 'dhk')
                ->where('price', '=', 12000);
        })
        ->prepare()
        ->get();

echo '<pre>';
dump($result);
