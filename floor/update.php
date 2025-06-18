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

$result = $conn->query("SELECT * FROM floor WHERE id = $id");
$floor = $result->fetch_assoc();

if (!$floor) {
    header("Location: read.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $note = trim($_POST['note'] ?? '');

    // Validation
    $errors = [];

    if (empty($name)) {
        $errors[] = 'Floor name is required';
    }
    if (empty($code)) {
        $errors[] = 'Floor code is required';
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            showToast($error, 'error');
        }
        // Store input for repopulating form
        $_SESSION['old_input'] = [
            'name' => $name,
            'code' => $code,
            'note' => $note
        ];
        $_SESSION['flash'] = [
            'type' => 'fail',
            'message' => 'Error updating floor: ' . implode(', ', $errors)
        ];
        header("Location: update.php");
        exit;
    }

    $stmt = $conn->prepare("UPDATE floor SET name=?, code=?, note=? WHERE id=?");
    if ($stmt->execute([$name, $code, $note, $id])) {
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
    <form action="update.php?id=<?= $id ?>" method="POST">
        <div class="grid grid-cols-1 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Floor Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($floor['name']) ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700">Floor Code</label>
                <input type="text" id="code" name="code" value="<?= htmlspecialchars($floor['code']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="note" class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea id="note" name="note" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($floor['note']) ?></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center">
                    <i class="fas fa-save mr-2"></i> Update Floor
                </button>
            </div>
        </div>
    </form>
</body>

</html>