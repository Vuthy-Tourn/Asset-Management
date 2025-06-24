<?php
require_once '../components/config/db.php';
require_once '../components/flash.php';
require_once '../components/datatable.php';

$flash = flash();
if ($flash) {
    require_once '../components/toast.php';
    showToast($flash['message'], $flash['type']);
}
    $floors = $conn->query("SELECT id, name FROM floor")->fetch_all(MYSQLI_ASSOC);

    $floorOptions = ['' => 'All Floors'] + array_column($floors, 'name', 'id');
$table = new DataTable($conn, [
    'table' => 'category',
    'primaryKey' => 'id',
    'columns' => [
        ['name' => 'id', 'label' => 'ID', 'nowrap' => true],
        ['name' => 'name', 'label' => 'Name'],
        ['name' => 'code', 'label' => 'Code', 'nowrap' => true],
        [
            'name' => 'floor_name',
            'label' => 'Floor',
            'format' => 'badge',
            'colors' => [
                'Ground' => 'purple',  // Must match exactly or implement case-insensitive matching
                'First' => 'cyan',
                'Second' => 'yellow',
                'Third' => 'green'
            ],
            'default_color' => 'gray',
            'nowrap' => true
        ],
        ['name' => 'created_at', 'label' => 'Created At', 'format' => 'date', 'nowrap' => true],
    ],
    'joins' => ['LEFT JOIN floor ON category.floor_id = floor.id'],
    'filterOptions' => [
        'floor_id' => $floorOptions
        ],
    'dateField' => 'category.created_at',
    'searchable' => ['category.name', 'category.code', 'floor.name'],
    'addButton' => true,
    'addButtonText' => 'Add Category',
    'addButtonModal' => 'createCategoryModal',
]);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include '../components/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Header -->
            <?php include '../components/header.php'; ?>

            <!-- Main Content -->
            <main class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold">Manage Categories</h1>
                    <button
                        id="addCategoryBtn"
                        data-modal-fetch="createCategoryModal"
                        data-modal-url="create.php"
                        data-modal-target="createCategoryContent"
                        class="bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-6 py-3 rounded-lg flex items-center transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="fas fa-plus mr-2"></i> Add New Category
                    </button>
                </div>

                <?php
                $table->render();
                ?>
            </main>
        </div>
    </div>
    <!-- Create Category Modal -->
    <div id="createCategoryModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center">
        <div class="modal-content bg-white p-6 rounded-lg max-w-2xl w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">Add New Category</h3>
                <button data-modal-close="createCategoryModal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    &times;
                </button>
            </div>
            <div id="createCategoryContent">
                <!-- Content will be loaded here from create.php -->
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="editCategoryModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center">
        <div class="modal-content bg-white p-6 rounded-lg max-w-2xl w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">Edit Category</h3>
                <button data-modal-close="editCategoryModal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    &times;
                </button>
            </div>
            <div id="editCategoryContent">
                <!-- Content will be loaded here from update.php -->
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="modal hidden fixed inset-0 items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-xl font-semibold text-red-600">Confirm Deletion</h3>
                <button data-modal-close="deleteConfirmModal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    &times;
                </button>
            </div>
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl mr-3"></i>
                    <p class="text-gray-700">Are you sure you want to delete this category? This action cannot be undone.</p>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button data-modal-close="deleteConfirmModal" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button id="confirmDeleteButton" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <?php if ($flash): ?>
        <div id="toast" class="toast fixed top-4 right-4 <?= $flash['type'] === 'success' ? 'bg-green-500' : 'bg-red-500' ?> text-white px-6 py-4 rounded-lg shadow-lg flex items-center max-w-sm">
            <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-3 text-xl"></i>
            <span><?= htmlspecialchars($flash['message']) ?></span>
            <button onclick="document.getElementById('toast').remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.getElementById('toast');
                if (toast) toast.remove();
            }, 5000);
        </script>
    <?php endif; ?>
    <script src="../js/modal.js"></script>
    <script src="../js/search.js"></script>
</body>

</html>