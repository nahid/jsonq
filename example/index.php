<?php
require '../vendor/autoload.php';

use Nahid\JsonQ\Jsonq;

$json=new Jsonq();
$json->import('../data.json');


$result = $json->find('users.5.visits.0.name');

            

echo '<pre>';
dump($result);
?>

