<?php

$rootDir = str_replace('\\', '/', dirname(__DIR__, 1)) . DIRECTORY_SEPARATOR;

require $rootDir . 'vendor/autoload.php';

use Nahid\JsonQ\Jsonq;


$result = '';
try {
    $json=new Jsonq($rootDir . 'data.json');
    try {
        $result = $json->from('products')
            ->where('cat', '=', 1)
            ->prepare()
            ->get();
    } catch (\Nahid\JsonQ\Exceptions\NullValueException $e) {
        echo "Node must have a value";
    }

} catch (\Nahid\JsonQ\Exceptions\FileNotFoundException $e) {
    echo "File not found";
} catch (\Nahid\JsonQ\Exceptions\InvalidJsonException $e) {
    echo "This file is not valid JSON";
}

echo '<pre>';
dump($result);
