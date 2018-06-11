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

$jq = new Jsonq('data1.json');

$result = $jq->from('data')
    ->pipe(function($j) {
        return $j->transform(function($val) {
            $val['user_id'] = $val['user']['id'];
            return $val;
        });
    })->get();

dump($result);