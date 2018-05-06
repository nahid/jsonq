<?php
require '../vendor/autoload.php';

use Nahid\JsonQ\Jsonq;

$json=new Jsonq();
$json->import('../data.json');
//$json->collect([2, 3, 7]);


$result = $json->find('users.1.name');
            

echo '<pre>';
dump($result);

