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

try {
    $result = $jq->from('data')
        ->pipe(function($j) {
            return $j->transform(function($val) {
                $val['user_id'] = $val['user']['id'];
                $val['issued_at'] = date('Y, M d', strtotime($val['issued_at']));
                $val['created_at'] = date('Y, M d h:i:s', strtotime($val['created_at']));
//                $val['balance'] = $val['balance'] * 80;
                return $val;
            });
        })
        //->select('user_id', 'number', 'balance')
        ->implode('balance', ' ');

    dump($result);
} catch (\Nahid\JsonQ\Exceptions\ConditionNotAllowedException $e) {

} catch (\Nahid\JsonQ\Exceptions\NullValueException $e) {

}