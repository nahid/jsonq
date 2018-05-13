<?php
/**
 * Example: exists()
 * ================================
 *
 * exists() method check processed data existence
 */

require_once '../vendor/autoload.php';

use Nahid\JsonQ\Jsonq;

$q = new Jsonq('data.json');

try {
    $res = $q->from('vendor.name')->exists();
    dump($res);
} catch (\Nahid\JsonQ\Exceptions\ConditionNotAllowedException $e) {
    echo $e->getMessage();
} catch (\Nahid\JsonQ\Exceptions\NullValueException $e) {
    echo $e->getMessage();
}
