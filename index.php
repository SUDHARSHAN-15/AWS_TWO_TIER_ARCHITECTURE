<?php include 'config.php'; 
$menu_result = $conn->query("SELECT * FROM menu ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Restaurant - Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body { background: #f8f9fa; }
        .card:hover { transform: scale(1.03); transition: 0.3s; }
        .card-img-top { height: 200px; object-fit: cover; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">My Restaurant 🍴</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="cart.php">Cart <span class="badge bg-danger cart-count"><?php echo count($_SESSION['cart']); ?></span></a>
            <a class="nav-link" href="orders.php">Orders</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1 class="text-center mb-4">Our Delicious Menu</h1>
    <div class="row">
        <?php while($item = $menu_result->fetch_assoc()): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow">
                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['name']); ?>">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars($item['description']); ?></p>
                    <p class="card-text fw-bold text-success">$<?php echo number_format($item['price'], 2); ?></p>
                    <div class="mt-auto">
                        <input type="number" id="qty_<?php echo $item['id']; ?>" class="form-control mb-2" value="1" min="1">
                        <button class="btn btn-primary w-100 add-to-cart" data-id="<?php echo $item['id']; ?>">Add to Cart</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.add-to-cart').click(function() {
        var id = $(this).data('id');
        var qty = $('#qty_' + id).val();
        $.post('action.php', { action: 'add', id: id, qty: qty }, function(data) {
            $('.cart-count').text(data.cart_count);
            alert('Item added to cart!');
        }, 'json');
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>
