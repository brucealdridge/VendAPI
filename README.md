vend api class
==============

This is a basic php class for using the api for vend (vendhq.com) Its at a really basic state but it does exactly what I need at the moment. Feel free to add any issues/bugs and send me any pull requests.


### Basic Usage

```php
require 'vendapi.php';
$vend = new vendapi('https://shopname.vendhq.com','username','password');
$products = $vend->getProducts();
```

*NB* this will only grab the first 20 or so results. To grab all results set `$vend->automatic_depage` to `true`

```php
$vend->automatic_depage = true;
$products = $vend->getProducts();
```

### Other cool stuff

```php
$vend->getProducts(array('active' => '1', 'since' => '2012-09-15 20:55:00'));
```
*NB* I had issues with the vend api when passing in `array('source_id'=>'hot-coffee')` .. instead of the expected no matches, it returned all matches. So be careful.

```php
$coffee = $vend->getProduct('42c2ccc4-fbf4-11e1-b195-4040782fde00');
echo $coffee->name; // outputs "Hot Coffee"
if ($product->getInventory() == 0) {
  $coffee->setInventory(10);
  $coffee->name = 'Iced Coffee';
  $coffee->save();
}
```

