<?php
require_once '../components/config/db.php';
require_once '../components/flash.php';
require_once '../components/toast.php';


$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header("Location: read.php");
    exit;
}

$result = $conn->query("SELECT * FROM floor WHERE id = $id");
$floor = $result->fetch_assoc();

if (!$floor) {
    header("Location: read.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name'] ?? '');
    $code = $conn->real_escape_string($_POST['code'] ?? '');
    $note = trim($_POST['note'] ?? '');

    // Validation
    $errors = [];

    if (empty($name)) {
        $errors[] = 'Floor name is required';
    }
    if (empty($code)) {
        $errors[] = 'Floor code is required';
    }

    $sql = "UPDATE floor SET 
            name = '$name', 
            code = '$code', 
            note = '$note' 
            WHERE id = $id";

    if ($conn->query($sql)) {
        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Floor updated successfully'
        ];
        header("Location: read.php");
        exit;
    } else {
        showToast('Failed to update floor', 'error');
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Floor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- Change action to update.php and add method="POST" -->
    <form action="update.php?id=<?= $id ?>" method="POST">
        <!-- Add hidden ID field for extra security -->
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="space-y-6">
            <!-- Name Field -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Floor Name <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-tag text-gray-400"></i>
                    </div>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($floor['name']) ?>"
                        required
                        class="form-input pl-10 block w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 py-2 px-4 border">
                </div>
            </div>

            <!-- Code Field -->
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                    Floor Code
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-code text-gray-400"></i>
                    </div>
                    <input type="text" id="code" name="code" value="<?= htmlspecialchars($floor['code']) ?>"
                        class="form-input pl-10 block w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 py-2 px-4 border">
                </div>
            </div>

            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                    Notes
                </label>
                <textarea id="note" name="note" rows="3" class="mt-1 block w-full rounded-md rounded-lg py-2 px-4 border border-gray-300 focus:border-green-500 focus:ring-green-500 shadow-sm"><?= htmlspecialchars($floor['note']) ?></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 pt-4">
                <a href="read.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors group">
                    <i class="fas fa-save mr-2 transition-transform group-hover:scale-110"></i> Update Floor
                </button>
            </div>
        </div>
    </form>
</body>

</html>