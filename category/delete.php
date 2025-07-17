<?php
require_once '../components/config/db.php';


$id = $_GET['id'] ?? 0;

if (!$id) {
    header("Location: read.php");
    exit;
}

// Check if there are products using this category
$stmt = $conn->query("SELECT COUNT(*) FROM products WHERE category_id = ?");
$productCount = $stmt->fetch_row()[0];

if ($productCount > 0) {
    flash('error', 'Cannot delete category with associated products');
    exit;
}

// Delete the category
if ($conn->query("DELETE FROM category WHERE id = ?") === TRUE) {
    flash('success', 'Category deleted successfully');
    header("Location: read.php");
    exit;
} else {
    flash('error', 'Failed to delete category');
}
exit;
