<?php

require '../vendor/autoload.php';

use Nahid\JsonQ\Jsonq;


$result = '';
Jsonq::macro('less', function ($payable, $val) {
    return $payable < $val;
});

Jsonq::macro('int', function ($payable, $val) {
    return is_integer($payable);
});

$jq = new Jsonq('data.json');

$result = $jq->from('products')
    ->where('cat', 'int', 0)

    ->sum('price');
dump($result);