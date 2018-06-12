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
        //->where('user.id', '=', 345101090)
//        ->sortBy('user.id', 'desc')
        ->countGroupBy('user.id')
        ->get();
    dump($result);
} catch (\Nahid\JsonQ\Exceptions\ConditionNotAllowedException $e) {

} catch (\Nahid\JsonQ\Exceptions\NullValueException $e) {

}