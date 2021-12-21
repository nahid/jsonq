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

//$jq = new Jsonq('data.json');

try {
    $data = file_get_contents('data.json');
    // This will remove unwanted characters.
// Check http://www.php.net/chr for details

    $result = jsonq($data)
        ->from('users')
        ->where('visits.year', '=', 2010)
        ->get();
    dump($result);
} catch (\Nahid\JsonQ\Exceptions\ConditionNotAllowedException $e) {

} catch (\Nahid\JsonQ\Exceptions\NullValueException $e) {

}
