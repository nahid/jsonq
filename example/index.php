<?php
require '../vendor/autoload.php';

use Nahid\JsonQ\Jsonq;

$json=new Jsonq();
$json->import('../data.json');
//$json->collect([2, 3, 7]);


$result = $json->from('products')
    ->where('cat', '=', 1)
    ->prepare()
    ->get();
            

echo '<pre>';
dump($result);

