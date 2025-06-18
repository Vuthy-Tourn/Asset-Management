<?php
require_once '../components/config/db.php';
require_once '../components/flash.php';

session_start();

// Fetch all categories for dropdown
$categories = [];
$result = $conn->query("SELECT * FROM category ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $code = $_POST['code'] ?? '';
    $category_id = $_POST['category_id'] ?? null;

    $stmt = $conn->prepare("INSERT INTO products (name, code, category_id) VALUES (?, ?, ?)");
    if ($stmt->execute([$name, $code, $category_id])) {
        flash('success', 'Product added successfully');
    } else {
        flash('error', 'Error adding product: ' . $conn->error);
    }
    header("Location: read.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <form action="create.php" method="POST">
        <div class="grid grid-cols-1 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Product Name *</label>
                <input type="text" id="name" name="name" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
            </div>
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700">Product Code</label>
                <input type="text" id="code" name="code"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
            </div>
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700">Category *</label>
                <select id="category_id" name="category_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded flex items-center">
                    <i class="fas fa-save mr-2"></i> Save Product
                </button>
            </div>
        </div>
    </form>
</body>

</html>