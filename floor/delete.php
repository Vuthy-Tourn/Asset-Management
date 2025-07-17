<?php
require_once '../components/config/db.php';
require_once '../components/flash.php';


$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header("Location: read.php");
    exit;
}

// Check if there are categories using this floor
$result = $conn->query("SELECT COUNT(*) FROM category WHERE floor_id = $id");
$categoryCount = $result->fetch_row()[0];

if ($categoryCount > 0) {
    flash('error', 'Cannot delete floor with associated categories');
    exit;
}

// Delete the floor
if ($conn->query("DELETE FROM floor WHERE id = $id") === TRUE) {
    flash('success', 'Floor deleted successfully');
    header("Location: read.php");
    exit;
} else {
    flash('error', 'Failed to delete floor');
}
exit;
