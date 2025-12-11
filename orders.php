<?php include 'config.php'; 
$groups_result = $conn->query("SELECT * FROM order_groups ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">My Restaurant 🍴</a>
    </div>
</nav>

<div class="container mt-5">
    <h1 class="text-center mb-4">Recent Orders</h1>
    <div class="accordion" id="ordersAccordion">
        <?php $i = 0; while($group = $groups_result->fetch_assoc()): $i++; ?>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $i; ?>">
                    <strong><?php echo $group['order_number']; ?></strong> - <?php echo htmlspecialchars($group['customer_name']); ?> 
                    (<?php echo htmlspecialchars($group['customer_email']); ?>) - Total: $<?php echo number_format($group['total_amount'], 2); ?> 
                    - <?php echo $group['created_at']; ?>
                </button>
            </h2>
            <div id="collapse<?php echo $i; ?>" class="accordion-collapse collapse" data-bs-parent="#ordersAccordion">
                <div class="accordion-body">
                    <table class="table table-sm">
                        <thead><tr><th>Item</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
                        <tbody>
                            <?php
                            $items = $conn->query("SELECT m.name, o.quantity, o.total_price FROM orders o JOIN menu m ON o.menu_id = m.id WHERE o.order_group_id = {$group['id']}");
                            while($item = $items->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['total_price'] / $item['quantity'], 2); ?></td>
                                <td>$<?php echo number_format($item['total_price'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php if ($i == 0): ?>
        <p class="text-center">No orders yet.</p>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
