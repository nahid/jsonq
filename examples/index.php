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
    $result = $jq->from('users')
        ->where('visits.year', '=', 2010)
        ->sum('visits.year');
    dump($result);
} catch (\Nahid\JsonQ\Exceptions\ConditionNotAllowedException $e) {

} catch (\Nahid\JsonQ\Exceptions\NullValueException $e) {

}