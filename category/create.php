<?php
require_once '../components/config/db.php';
require_once '../components/flash.php';

session_start();

// Fetch all floors for dropdown
$floors = [];
$result = $conn->query("SELECT * FROM floor ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $floors[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name'] ?? '');
    $code = $conn->real_escape_string($_POST['code'] ?? '');
    $floor_id = intval($_POST['floor_id'] ?? 0);

    $sql = "INSERT INTO category (name, code, floor_id) VALUES ('$name', '$code', $floor_id)";

    if ($conn->query($sql)) {
        flash('success', 'Category added successfully');
        exit;
    } else {
        flash('error', 'Error adding category: ' . $conn->error);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Category</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <form action="create.php" method="POST">
        <div class="grid grid-cols-1 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Category Name *</label>
                <input type="text" id="name" name="name" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
            </div>

            <div>
                <label for="code" class="block text-sm font-medium text-gray-700">Category Code</label>
                <input type="text" id="code" name="code"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
            </div>

            <div>
                <label for="floor_id" class="block text-sm font-medium text-gray-700">Floor *</label>
                <select id="floor_id" name="floor_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                    <option value="">-- Select Floor --</option>
                    <?php foreach ($floors as $floor): ?>
                        <option value="<?= $floor['id'] ?>"><?= htmlspecialchars($floor['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded flex items-center">
                    <i class="fas fa-save mr-2"></i> Save Category
                </button>
            </div>
        </div>
    </form>
</body>

</html>