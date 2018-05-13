<?php
/**
 * Example: groupBy($column = null)
 * ================================
 *
 * groupBy() method helps you to getting grouped data from your
 * desire node/path
 */

require_once '../vendor/autoload.php';

use Nahid\JsonQ\Jsonq;

$q = new Jsonq('data.json');

try {
    $res = $q->from('users')->groupBy('location')->get();
    dump($res);
} catch (\Nahid\JsonQ\Exceptions\ConditionNotAllowedException $e) {
    echo $e->getMessage();
} catch (\Nahid\JsonQ\Exceptions\NullValueException $e) {
    echo $e->getMessage();
} catch (\Nahid\JsonQ\Exceptions\InvalidNodeException $e) {
    echo $e->getMessage();
}
