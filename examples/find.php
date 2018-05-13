<?php
/**
 * Example: find($node)
 * ======================
 *
 * To find your desire data from specific path
 * you can use this method. This method used as direct array or object accessor
 */

require_once '../vendor/autoload.php';

use Nahid\JsonQ\Jsonq;

$q = new Jsonq('data.json');

try {
    $res = $q->find('vendor.name');
    dump($res);
} catch (\Nahid\JsonQ\Exceptions\ConditionNotAllowedException $e) {
    echo $e->getMessage();
} catch (\Nahid\JsonQ\Exceptions\NullValueException $e) {
    echo $e->getMessage();
}
