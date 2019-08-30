<?php 
session_start();
$product_ids = [];
// session_destroy();

// Check if Add to Cart btn has been submitted
if(filter_input(INPUT_POST,'add_to_cart')) {
    if(isset($_SESSION['shopping_cart'])){
        // count the items in cart for indexing purposes
        $count = count($_SESSION['shopping_cart']);
        // create sequential array for matching array keys to product ids
        $product_ids = array_column($_SESSION['shopping_cart'],'id');
        print_pretty_array($product_ids);
        // check if the product with id exists in product_ids 
        if(!in_array(filter_input(INPUT_GET,'id'),$product_ids)){
            $_SESSION['shopping_cart'][$count] = [
                'id' => filter_input(INPUT_GET,'id'),
                'name' => filter_input(INPUT_POST,'name'),
                'price' => filter_input(INPUT_POST,'price'),
                'quantity' => filter_input(INPUT_POST,'quantity') 
            ];
        } else { // product exists, increase quantity
            // match array key to the product being added to the cart
            for ($i = 0; $i < count($product_ids); $i++) {
                if ($product_ids[$i] == filter_input(INPUT_GET,'id')) {
                    // Add item quanity to the existing product in the array
                    $_SESSION['shopping_cart'][$i]['quantity'] += filter_input(INPUT_POST,'quantity');
                }
            }
        }


    } else { // if shopping cart doesnt exist -> create first product with array key 0
        // create array using submitted from data, start from key 0 and fill it in with values
        $_SESSION['shopping_cart'][0] = [
            'id' => filter_input(INPUT_GET,'id'),
            'name' => filter_input(INPUT_POST,'name'),
            'price' => filter_input(INPUT_POST,'price'),
            'quantity' => filter_input(INPUT_POST,'quantity')
        ];    
    }
}

if(filter_input(INPUT_GET,'action') == 'delete') {
    foreach ($_SESSION['shopping_cart'] as $key => $product) {
        if ($product['id'] == filter_input(INPUT_GET, 'id')) {
            // remove prouct from cart when it matches get id 
            unset($_SESSION['shopping_cart'][$key]);
        }
    }
    // reset session array keys so they match with product_ids numeric array
    $_SESSION['shopping_cart'] = array_values($_SESSION['shopping_cart']);
}

 print_pretty_array($_SESSION);

function print_pretty_array($array){
    echo '<pre>';
    print_r($array);
    echo '</pre>';

}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Shopping Cart</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap-theme.min.css" integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
    
    <link rel="stylesheet" href="cart.css">
</head>
<body>
    <div class="container">
    <?php
    $connect = mysqli_connect('localhost','root','root','cart');
    $query = 'SELECT * FROM products ORDER BY id ASC';
    $result = mysqli_query($connect,$query);
    
    if($result):
        if(mysqli_num_rows($result)>0){
            while($product = mysqli_fetch_assoc($result)):
                ?>
                <div class="col-sm-4 col-md-3">
                    <form action="cart.php?action=add&id=<?php echo $product['id'];?>" method="post">
                        <div class="products">
                            <img src="img/<?php echo $product['image']; ?>" class="img-responsive">
                            <h4 class="text-info"><?php echo $product['name']; ?></h4>
                            <h4>$ <?php echo $product['price']; ?></h4>
                            <input type="text" name="quantity" class="form-control" value="1">
                            <input type="hidden" name="name" value="<?php echo $product['name']; ?>">
                            <input type="hidden" name="price" value="<?php echo $product['price']; ?>">
                            <input type="submit" name="add_to_cart" class="btn btn-info" style="margin-top:5px" value="Add to Cart">
                                
                        </div>
                    </form>
                
                </div>

                <?php
            endwhile;
        }
    endif;
    ?>
    <div style="clear:both"></div>
    <br />
    <div class="table-responsive">
        <table class="table">
        <tr><th colspan="5"><h3>Order Details</h3></th></tr>
        <tr>
            <th width="40%">Product Name</th>
            <th width="10%">Quanity</th>
            <th width="20%">Price</th>
            <th width="15%">Total</th>
            <th width="5%">Action</th>
        </tr>
        <?php 
        if(!empty($_SESSION['shopping_cart'])):
            $total = 0;
            foreach($_SESSION['shopping_cart'] as $key => $product):
        ?>
        <tr>
                <td><?php echo $product['name']; ?></td>
                <td><?php echo $product['quantity']; ?></td>
                <td><?php echo $product['price']; ?></td>
                <td><?php echo number_format($product['quantity'] * $product['price'],2); ?></td>
                <td>
                    <a href="cart.php?action=delete&id=<?php echo $product['id'];?>">
                        <div class="btn-danger">Remove</div>
                    </a>
                </td>
        </tr>
        <?php 
            $total = $total + ($product['quantity'] * $product['price']);
            endforeach;

        ?> 
        <tr>
            <td align="right" colspan="3">Total</td>
            <td align="right"><?php echo number_format($total,2)?></td>
            <td></td>
        </tr>
        <tr>
            <!-- Show checkout button only if the shopping cart is not empty -->
            <td colspan="5">
            <?php
                if (isset($_SESSION['shopping_cart'])):
                if (count($_SESSION['shopping_cart'] > 0)):     
            ?>
                <a href="#" class="button">Checkout</a>
                <?php endif; endif;?>
            </td>
        
        </tr>
        <?php 
        endif;
        ?>
        
        <!-- <tr>
            <td colspan="3" align="right">Total</td>
        </tr> -->
        

        </table>
    </div>
    </div>
</body>
</html>
