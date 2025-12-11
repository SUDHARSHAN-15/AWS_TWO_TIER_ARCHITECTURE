<?php
include 'config.php';

$action = $_POST['action'] ?? '';

if ($action == 'add') {
    $id = intval($_POST['id']);
    $qty = intval($_POST['qty'] ?? 1);
    $result = $conn->query("SELECT * FROM menu WHERE id = $id");
    $item = $result->fetch_assoc();

    $found = false;
    foreach ($_SESSION['cart'] as &$cart_item) {
        if ($cart_item['id'] == $id) {
            $cart_item['qty'] += $qty;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $_SESSION['cart'][] = ['id' => $id, 'name' => $item['name'], 'price' => $item['price'], 'qty' => $qty];
    }
    echo json_encode(['cart_count' => count($_SESSION['cart'])]);
}

elseif ($action == 'remove') {
    $id = intval($_POST['id']);
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $id) unset($_SESSION['cart'][$key]);
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    echo json_encode(['cart_count' => count($_SESSION['cart'])]);
}

elseif ($action == 'update') {
    $id = intval($_POST['id']);
    $qty = max(1, intval($_POST['qty']));
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $id) $item['qty'] = $qty;
    }
    echo json_encode(['cart_count' => count($_SESSION['cart'])]);
}

elseif ($action == 'load') {
    $html = '<table class="table"><thead><tr><th>Item</th><th>Price</th><th>Qty</th><th>Total</th><th></th></tr></thead><tbody>';
    $grand_total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal = $item['price'] * $item['qty'];
        $grand_total += $subtotal;
        $html .= "<tr>
            <td>{$item['name']}</td>
            <td>\${$item['price']}</td>
            <td><input type='number' class='form-control qty-input' data-id='{$item['id']}' value='{$item['qty']}' min='1' style='width:70px'></td>
            <td>\$" . number_format($subtotal, 2) . "</td>
            <td><button class='btn btn-danger btn-sm remove-item' data-id='{$item['id']}'>Remove</button></td>
        </tr>";
    }
    $html .= "<tr><td colspan='3'><strong>Grand Total</strong></td><td colspan='2'><strong>\$" . number_format($grand_total, 2) . "</strong></td></tr></tbody></table>";
    $html .= '<div class="mt-4">
        <input type="text" id="customer_name" class="form-control mb-2" placeholder="Your Name" required>
        <input type="email" id="customer_email" class="form-control mb-2" placeholder="Your Email" required>
        <button id="place-order" class="btn btn-success btn-lg w-100">Place Order</button>
    </div>';
    if (empty($_SESSION['cart'])) $html = '<p class="text-center">Your cart is empty. <a href="index.php">Add items</a></p>';
    echo json_encode(['html' => $html, 'cart_count' => count($_SESSION['cart'])]);
}

elseif ($action == 'place') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $total = 0;
    foreach ($_SESSION['cart'] as $item) $total += $item['price'] * $item['qty'];

    // Generate order number
    $count_result = $conn->query("SELECT COUNT(*) FROM order_groups");
    $next_num = $count_result->fetch_row()[0] + 1;
    $order_number = 'ORD-' . str_pad($next_num, 3, '0', STR_PAD_LEFT);

    // Create group
    $conn->query("INSERT INTO order_groups (order_number, customer_name, customer_email, total_amount) 
                  VALUES ('$order_number', '$name', '$email', $total)");
    $group_id = $conn->insert_id;

    // Insert items
    foreach ($_SESSION['cart'] as $item) {
        $item_total = $item['price'] * $item['qty'];
        $conn->query("INSERT INTO orders (order_group_id, customer_name, customer_email, menu_id, quantity, total_price) 
                      VALUES ($group_id, '$name', '$email', {$item['id']}, {$item['qty']}, $item_total)");
    }

    $_SESSION['cart'] = [];
    echo json_encode(['order_number' => $order_number, 'cart_count' => 0]);
}
$conn->close();
?>
