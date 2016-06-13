# JsonQ (JSON Query)
JsonQ is a library for JSON data query  for PHP and it also support laravel. You can import JSON file from anywhere in your project. Its handle and query your imported JSON data magically. So lets enjoy it.

## Installation
JsonQ is a standalone package but it also support Laravel. To install this package run this command in your terminal from your desire project.

```
composer require nahid/jsonq
```
Instead, you may install it manually

```
{
    "require": {
        "nahid/jsonq": "^1.0"
    }
}
```

#### For laravel 
Once JsonQ is installed, you need to register the service provider. Open up `config/app.php` and add the following to the providers key.
```php
'Nahid\JsonQ\JsonqServiceProvider'
```
You can register the `Jsonq` facade in the aliases key of your `config/app.php` file if you like.
```php
'Jsonq' => 'Nahid\JsonQ\Facades\Jsonq'
```

## Configuration
There are no configuration if you use it without laravel. If you using laravel then you have to run this command to publish JsonQ config

```
php artisan vendor:publish --provider="Nahid\JsonQ\JsonqServiceProvider"
```
This will create `config/jsonq.php` file in your app that you can modify to set your desire configuration.

```php
return [
	'json'=>[
		'storage_path'=>database_path()
	]
];
```
Here storage path means where you want to store all json data by default. Its not mandatory.

## Usage
JsonQ has many convenient method to handle and query json data. So lets start how to use this package. Suppose you have a JSON file `data.json` and this is like the format

```json
{
	"name": "products",
	"description": "Features product list",
	"vendor":{
		"name": "Computer Source BD",
		"email": "info@example.com",
		"website":"www.example.com"
	},
	"items":[
		{"id":1, "name":"MacBook Pro 13 inch retina", "price": 1500},
		{"id":2, "name":"MacBook Pro 15 inch retina", "price": 1700},
		{"id":3, "name":"Sony VAIO", "price": 1200},
		{"id":4, "name":"Fujitsu", "price": 850}
	]
}
```
and you want to get description from this JSON data. So what you have to do? Don't be afraid, its so easy.  First you have to import JSON data from file.
```php
require 'vendor/autoload.php';

use Nahid\JsonQ\Jsonq;

$json=new Jsonq('path/to/data.json');

//or you may use
//$json->import('path/to/data.json');

$result = $json->node('description')->get();
echo $result;
```

#### For laravel
```php
use Nahid\JsonQ\Facades\Jsonq;

Jsonq::import('path/to/data.json');

$result = Jsonq::node('description')->get();
echo $result;
```
The result is 
> Features product list

You also get nested node value by using colon ':' separator
```php
$result = $json->node('vendor:email')->get();
echo $result;
```
Here is the result
> info@example.com

### Using Condition
You may also use condition but make sure this data is already formatted. JsonQ applied condition over formatted data.

Suppose we want an item where id is 2
```php
$result = $json->node('items')->where('id', '=', 2)->get();
```
Here return a resultant array. But you you can get a single object for a single entity .
```php
$result = $json->node('items')->where('id', '=', 2)->first();
```
Its return the first entity as an object.

### Using Multiple Conditions
If we want a data where price greater than 1000 and less than 1600 then what should we do?
```php
$result = $json->node('items')->where('price', '>', 1000)->where('price', '<', 1600)->get();
```
So here we'll get an array which price is greater than 1000 and less than 1600

### Using orWhere

Don't worry here is orWhere. 
```php
$result = $json->node('items')->where('price', '==', 1200)->orWhere('id', '=', 3)->get();
```