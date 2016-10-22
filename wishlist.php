<?php
// Turn on error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required classes
require_once('classes/Inventory.php');
require_once('classes/Cart.php');
require_once('classes/Product.php');

// Make inventory
$inventory = new Inventory();



$cart = new Cart($inventory, 'cart', $allow_out_of_stock_items=false);
$wishlist = new Cart($inventory, 'wishlist', $allow_out_of_stock_items = true);



?>

<html>
    <head>
        <title>My Store</title>
        <link href="css/index.css" rel="stylesheet">
        <link href="css/checkout.css" rel="stylesheet">
    </head>
    <body>
        <div class="container">
            <div class="wrapper">
            <h1>My Store</h1>
            <hr>
            <p  class="lead" id="continue"><a href="index.php" title="Continue Shopping">Continue Shopping >></a></p>
  
            <h2>My Wishlist</h2>
            <div id="wishlist" class="clearer">
                <ul>
                    <?php
                    if ($wishlist->items_in_cart) {
                        $errors = $wishlist->errors;
                        
                        foreach ($wishlist->items_in_cart as $key => $value) {
                            $product = $inventory->getItem($key);
                            $is_in_stock = $product->quantity_in_stock? true: false; 
                            echo '<li><span class="title">' . $product->name . '</span>' . 
                                    '<a href="checkout.php?action=remove&target=wishlist&id=' . $product->id . '" title="Remove">Remove</a> '
                                    .($is_in_stock? '<a href="checkout.php?action=move&source=wishlist&target=cart&id=' . $product->id . '" title="Move to Cart">Move to Cart</a> ': '<span class="alert">Out of Stock</span>')
                                    . '<span class="price"> $' . number_format($value * $product->finalPrice(), 2) 
                                    .'</span></li>';
                            if (isset($errors['item' . $key])) {
                                echo '<p class="error">' . $errors['item' . $key] . '</p>';
                            }
                        }
                    }
                    ?>

                </ul>
            </div>
        </div>
      </div>
    </body>
</html>