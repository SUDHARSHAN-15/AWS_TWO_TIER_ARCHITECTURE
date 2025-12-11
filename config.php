<?php
session_start();  // Important: Start session here for cart

$host = 'database-1.cdmeihzpu5cy.us-west-2.rds.amazonaws.com';
$user = 'admin';
$pass = 'YOUR_RDS_PASSWORD_HERE';  // Set in environment or keep secret
$db = 'testdb';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>
