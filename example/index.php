<?php
require '../vendor/autoload.php';

use Nahid\JsonQ\Jsonq;

$json=new Jsonq();
$json->import('../data.json');


$result = $json
			->from('users')
			//->where('id', '=', 6)
            //->then('visits')
            //->where('year', '=', 2011)
            ->fetch()
            ->each(function($key, $val) {
                echo $key . ' = ' .$val['name'] . '<br/>';
            });

            

echo '<pre>';
dump($result);

