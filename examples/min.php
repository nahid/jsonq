<?php
/**
 * Example: min($column = null)
 * ================================
 *
 * min() method return minimum value of resulting data
 */

require_once '../vendor/autoload.php';

use Nahid\JsonQ\Jsonq;

$q = new Jsonq('data.json');

try {
    $res = $q
        ->from('products')
        ->where('cat', '=', 2)
        ->min('price');
    
    //from plain array collection
    $res1 = $q->collect([2, 10, 1, 5, 7])
            ->min();
    dump($res, $res1);
} catch (\Nahid\JsonQ\Exceptions\ConditionNotAllowedException $e) {
    echo $e->getMessage();
} catch (\Nahid\JsonQ\Exceptions\NullValueException $e) {
    echo $e->getMessage();
}
