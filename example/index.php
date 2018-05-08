<?php

$rootDir = str_replace('\\', '/', dirname(__DIR__, 1)) . DIRECTORY_SEPARATOR;

require $rootDir . 'vendor/autoload.php';

use Nahid\JsonQ\Jsonq;


$result = '';
    $json=new Jsonq($rootDir . 'data.json');
    $result = $json->from('products')
        ->prepare()
        ->hasOne('users', 'id', '=', 'user_id')
        ->hasOne('categories', 'id', '=', 'category_id')
       ->get();

echo '<pre>';
dump($result);
