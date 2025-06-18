<?php
require_once '../components/config/db.php';
require_once '../components/flash.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $code = $_POST['code'] ?? '';
    $note = $_POST['note'] ?? '';

    $stmt = $conn->prepare("INSERT INTO floor (name, code, note) VALUES (?, ?, ?)");
    if ($stmt->execute([$name, $code, $note])) {
        flash('success', 'Floor added successfully');
    } else {
        flash('error', 'Error adding floor: ' . $conn->error);
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
    <title>Add New Floor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <form action="create.php" method="POST">
        <div class="grid grid-cols-1 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Floor Name</label>
                <input type="text" id="name" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="code" class="block text-sm font-medium text-gray-700">Floor Code</label>
                <input type="text" id="code" name="code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="note" class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea id="note" name="note" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center">
                    <i class="fas fa-save mr-2"></i> Save Floor
                </button>
            </div>
        </div>
    </form>
</body>

</html>