<?php
require '../vendor/autoload.php';

use Nahid\JsonQ\Jsonq;

$json=new Jsonq();
$json->import('../data.json');


$result = $json
			->node('users')
            ->where('id', '=', 6)->get();

            

echo '<pre>';
var_dump($result);
?>

