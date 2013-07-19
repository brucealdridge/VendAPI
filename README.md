Vend API class
==============

This is a basic PHP class for using the API for Vend (vendhq.com). It
is at a really basic state but it does exactly what I need at the
moment. Feel free to add any issues/bugs and send me any pull
requests.


### Basic Usage

```php
require 'vendapi.php';
$vend = new VendAPI\VendAPI('https://shopname.vendhq.com','username','password');
$products = $vend->getProducts();
```

*NB* this will only grab the first 20 or so results. To grab all results set `$vend->automatic_depage` to `true`

```php
$vend->automatic_depage = true;
$products = $vend->getProducts();
```
### Add a Product

```php
$donut = new \VendAPI\VendProduct(null, $vend);
$donut->handle = 'donut01';
$donut->sku = '343434343';
$donut->retail_price = 2.99;
$donut->name = 'Donut w/ Sprinkles';
$donut->save();
echo 'Donut product id is '.$donut->id;
```

### Add a Sale

```php
$sale = new \VendAPI\VendSale(null, $vend);
$sale->register_id = $register_id;
$sale->customer_id = $customer_id;
$sale->status = 'OPEN';
$products = array();
foreach ($items as $item) {
    $products[] = array(
        'product_id' => $item->product_id,
        'quantity' => $item->quantity,
        'price' => $item->price
    );
}
$sale->register_sale_products = $products;
$sale->save();

echo "Created new order with id: ".$sale->id;
```

### Other cool stuff

```php
$vend->getProducts(array('active' => '1', 'since' => '2012-09-15 20:55:00'));
```
*NB* Check the vend api docs for supported search fields. If a search field isn't supported all results will be returned rather than the zero I was expecting

```php
$coffee = $vend->getProduct('42c2ccc4-fbf4-11e1-b195-4040782fde00');
echo $coffee->name; // outputs "Hot Coffee"
if ($product->getInventory() == 0) {
  $coffee->setInventory(10);
  $coffee->name = 'Iced Coffee';
  $coffee->save();
}
```

### Debugging

To debug make a call to the ```debug()``` function.
eg:
```php
$vend->debug(true);
```
