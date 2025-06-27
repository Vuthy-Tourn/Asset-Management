<?php
require_once '../components/config/db.php';
require_once '../components/flash.php';
require_once '../components/toast.php';

session_start();
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header("Location: read.php");
    exit;
}

// Fetch category to edit
$result = $conn->query("SELECT * FROM category WHERE id = $id");
$category = $result->fetch_assoc();

if (!$category) {
    header("Location: read.php");
    exit;
}

// Get all floors for dropdown
$floors = [];
$floor_result = $conn->query("SELECT * FROM floor ORDER BY name");
while ($row = $floor_result->fetch_assoc()) {
    $floors[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name'] ?? '');
    $code = $conn->real_escape_string($_POST['code'] ?? '');
    $floor_id = intval($_POST['floor_id'] ?? 0);

    $sql = "UPDATE category SET 
            name = '$name', 
            code = '$code', 
            floor_id = $floor_id 
            WHERE id = $id";

    if ($conn->query($sql)) {
        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Category updated successfully'
        ];
        header("Location: read.php");
        exit;
    } else {
        showToast('Failed to update category', 'error');
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-input {
            transition: all 0.3s ease;
        }

        .form-input:focus {
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }
    </style>
</head>

<body>
    <form action="update.php?id=<?= $id ?>" method="POST">
        <div class="space-y-6">
            <!-- Name Field -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Category Name <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-tag text-gray-400"></i>
                    </div>
                    <input type="text" id="name" name="name"
                        value="<?= htmlspecialchars($category['name']) ?>"
                        required
                        class="form-input pl-10 block w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 py-2 px-4 border">
                </div>
            </div>

            <!-- Code Field -->
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                    Category Code
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-code text-gray-400"></i>
                    </div>
                    <input type="text" id="code" name="code"
                        value="<?= htmlspecialchars($category['code']) ?>"
                        class="form-input pl-10 block w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 py-2 px-4 border">
                </div>
            </div>

            <!-- Floor Select -->
            <div>
                <label for="floor_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Floor
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-layer-group text-gray-400"></i>
                    </div>
                    <select id="floor_id" name="floor_id"
                        class="form-input pl-10 block w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 py-2 px-4 border appearance-none">
                        <option value="">-- Select Floor --</option>
                        <?php foreach ($floors as $floor): ?>
                            <option value="<?= $floor['id'] ?>"
                                <?= $floor['id'] == $category['floor_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($floor['name']) ?>
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
                    <i class="fas fa-save mr-2 transition-transform group-hover:scale-110"></i> Update Category
                </button>
            </div>
        </div>
    </form>
</body>

</html>