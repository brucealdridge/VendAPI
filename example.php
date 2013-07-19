<?php

include 'src/VendAPI/VendAPI.php';

$vend = new VendAPI\VendAPI('https://shopname.vendhq.com','username','password');

$products = $vend->getProducts();

$donut = new \VendAPI\VendProduct(null, $vend);
$donut->handle = 'donut01';
$donut->sku = '343434343';
$donut->retail_price = 2.99;
$donut->name = 'Donut w/ Sprinkles';
$donut->save();
echo 'Donut product id is '.$donut->id;
