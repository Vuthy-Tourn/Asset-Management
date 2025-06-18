<?php
require_once '../components/config/db.php';
require_once '../components/flash.php';
require_once '../components/toast.php';

session_start();

// Input validation
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    showToast('Invalid product ID', 'error');
    header("Location: read.php");
    exit;
}

// Get product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    showToast('Product not found', 'error');
    header("Location: read.php");
    exit;
}

// Get categories
$categories = [];
$category_result = $conn->query("SELECT * FROM category ORDER BY name");
while ($row = $category_result->fetch_assoc()) {
    $categories[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $category_id = $_POST['category_id'] ?? null;

    // Validation
    $errors = [];

    if (empty($name)) {
        $errors[] = 'Product name is required';
    }

    if (empty($category_id)) {
        $errors[] = 'Please select a category';
    }


    if (!empty($errors)) {
        foreach ($errors as $error) {
            showToast($error, 'error');
        }
        // Store input for repopulating form
        $_SESSION['old_input'] = [
            'name' => $name,
            'code' => $code,
            'category_id' => $category_id
        ];
        $_SESSION['flash'] = [
            'type' => 'fail',
            'message' => 'Error updating product: ' . implode(', ', $errors)
        ];
        header("Location: update.php");
        exit;
    }

    // Update product
    $stmt = $conn->prepare("UPDATE products SET name = ?, code = ?, category_id = ? WHERE id = ?");
    if ($stmt->execute([$name, $code, $category_id, $id])) {
        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Product updated successfully'
        ];
        header("Location: read.php");
        exit;
    } else {
        showToast('Failed to update product', 'error');
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<form action="update.php?id=<?= $id ?>" method="POST">
    <div class="grid grid-cols-1 gap-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
        </div>
        <div>
            <label for="code" class="block text-sm font-medium text-gray-700">Product Code</label>
            <input type="text" id="code" name="code" value="<?= htmlspecialchars($product['code']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
        </div>
        <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
            <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded flex items-center">
                <i class="fas fa-save mr-2"></i> Update Product
            </button>
        </div>
    </div>
</form>

</html>