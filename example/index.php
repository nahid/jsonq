<?php

$rootDir = str_replace('\\', '/', dirname(__DIR__, 1)) . DIRECTORY_SEPARATOR;

require $rootDir . 'vendor/autoload.php';

use Nahid\JsonQ\Jsonq;


$result = '';
    $json=new Jsonq($rootDir . 'data.json');
    $result = $json->from('products')
        ->prepare()
        ->pipe(function($json, $map) {
            $data = [];
            foreach($map as $key => $m) {
                if ($m['id']%2 == 1) {
                    $data[] = $m;
                }
            }

            return $data;
        })->get();

echo '<pre>';
dump($result);
