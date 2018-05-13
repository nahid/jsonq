<?php
/**
 * Example: copy($node)
 * ======================
 *
 * copy() make deep copy of current instance of Jsonq.
 */

require_once '../vendor/autoload.php';

use Nahid\JsonQ\Jsonq;

$q = new Jsonq('data.json');

try {
    $res = $q->from('users')->get();

    // creating new copy of Jsonq instance
    $q2 = $q->copy();
    $res2 = $q2->reset()->find('vendor.email');
    dump($res, $res2);
} catch (\Nahid\JsonQ\Exceptions\ConditionNotAllowedException $e) {
    echo $e->getMessage();
} catch (\Nahid\JsonQ\Exceptions\NullValueException $e) {
    echo $e->getMessage();
}
