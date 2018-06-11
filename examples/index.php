<?php

require '../vendor/autoload.php';

use Nahid\JsonQ\Jsonq;


//$result = '';
//Jsonq::macro('less', function ($payable, $val) {
//    return $payable < $val;
//});
//
//Jsonq::macro('int', function ($payable, $val) {
//    return is_integer($payable);
//});
//
$jq = new Jsonq('data.json');

$result = $jq->from('products')
    ->select('name', 'id')
    ->where('cat', '=', 2)
    ->sortBy('user_id', 'asc')
    ->get();
dump($result);