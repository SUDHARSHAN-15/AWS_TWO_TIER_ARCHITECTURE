<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">My Restaurant 🍴</a>
        <a class="nav-link ms-auto text-white" href="orders.php">View Orders</a>
    </div>
</nav>

<div class="container mt-5">
    <h1>Your Cart</h1>
    <div id="cart-content"></div>
</div>

<script>
function loadCart() {
    $.post('action.php', { action: 'load' }, function(data) {
        $('#cart-content').html(data.html);
        $('.cart-count').text(data.cart_count);
    }, 'json');
}
$(document).ready(function() {
    loadCart();

    $(document).on('change', '.qty-input', function() {
        $.post('action.php', { action: 'update', id: $(this).data('id'), qty: $(this).val() }, loadCart);
    });

    $(document).on('click', '.remove-item', function() {
        $.post('action.php', { action: 'remove', id: $(this).data('id') }, loadCart);
    });

    $(document).on('click', '#place-order', function() {
        var name = $('#customer_name').val();
        var email = $('#customer_email').val();
        if (name && email) {
            $.post('action.php', { action: 'place', name: name, email: email }, function(data) {
                alert('Order placed successfully! Your Order Number: ' + data.order_number);
                loadCart();
            }, 'json');
        } else {
            alert('Please enter name and email');
        }
    });
});
</script>
</body>
</html>
