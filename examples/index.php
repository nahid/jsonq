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

try {
    $result = $jq->from('products')
        ->where('user_id', 1)
        ->get();
} catch (\Nahid\QArray\ConditionNotAllowedException $e) {

} catch (\Nahid\QArray\NullValueException $e) {

}