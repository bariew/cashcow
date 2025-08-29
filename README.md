### cashcow API client.

Description
-----------
Interacts with cashcow stores API


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist bariew/cashcow
```

or add

```
"bariew/cashcow": "dev-master"
```

to require the section of your `composer.json` file.


Usage
-----

```
$cashcow = Api::instance($id, $token);
// get single Order data  
$order = $cashcow->orders(['OrderID' => $id])[0];
// update Order
$cashcow->orderUpdate($order->order_id, $order->email, [
    'order_status_type' => $cashcow::STATUS_DELIVERED,
    'order_notes' => "delivered",
]);
// update Product
$cashcow->productUpdate('9201B', ['qty' => 10]);
```