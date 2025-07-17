<?php
require_once '../components/config/db.php';
require_once '../components/flash.php';
require_once '../components/toast.php';



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

<!-- Form -->
<form action="update.php?id=<?= $id ?>" method="POST">
    <div class="space-y-6">
        <!-- Name Field -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                Product Name <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-tag text-gray-400"></i>
                </div>
                <input type="text" id="name" name="name"
                    value="<?= htmlspecialchars($product['name']) ?>"
                    required
                    class="form-input pl-10 block w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 py-2 px-4 border">
            </div>
        </div>

        <!-- Code Field -->
        <div>
            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                Product Code
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-code text-gray-400"></i>
                </div>
                <input type="text" id="code" name="code"
                    value="<?= htmlspecialchars($product['code']) ?>"
                    class="form-input pl-10 block w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 py-2 px-4 border">
            </div>
        </div>

        <!-- category Select -->
        <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                Category
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-layer-group text-gray-400"></i>
                </div>
                <select id="category_id" name="category_id"
                    class="form-input pl-10 block w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 py-2 px-4 border appearance-none">
                    <option value="">-- Select category --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"
                            <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3 pt-4">
            <a href="read.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                <i class="fas fa-times mr-2"></i> Cancel
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors group">
                <i class="fas fa-save mr-2 transition-transform group-hover:scale-110"></i> Update Product
            </button>
        </div>
    </div>
</form>

</html>