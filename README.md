# DealNews API Client for PHP

A php client for accessing the DealNews api


## Basic Example

```php
require 'vendor/autoload.php';

$client = new \DealNews\API\Client\HTTP ("YOUR_API_KEY");

// perform a simple get request to an api endpoint
$response = $client->get("/features");

// echoes 200
echo $response['status'];

// dumps out all response headers
var_dump($response['headers']);

// echoes response body
echo $response['body'];
```
