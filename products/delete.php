<?php
require_once '../components/config/db.php';
require_once '../components/flash.php';

session_start();

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    flash('error', 'Invalid product ID');
    header("Location: read.php");
    exit;
}

$id = (int)$id;

// Delete the product
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
if ($stmt->execute([$id])) {
    flash('success', 'Product deleted successfully');
} else {
    flash('error', 'Failed to delete product');
}

header("Location: read.php");
exit;
