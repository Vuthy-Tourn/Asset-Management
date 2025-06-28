<?php
require_once '../components/config/db.php';
require_once '../components/flash.php';
require_once '../components/datatable.php';
require_once '../components/page_header.php';
require_once '../components/modal.php';

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
                'Third' => 'green',
                'Fourth' => 'sky',
                'Fifth' => 'fuchsia'
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
                <!-- Page Header -->
                <?php
                renderPageHeader(
                    'Category Management',
                    "Manage your Category information",
                    [
                        'text' => 'Add New Category',
                        'icon' => 'fa-plus',
                        'modalId' => 'createCategory',
                        'modalUrl' => 'create.php',
                        'modalTarget' => 'createCategoryContent'
                    ],
                    [
                        ['title' => 'Dashboard', 'url' => '/Uni-PHP/Assignment/index.php'],
                        ['title' => 'Categories'] // Current page (no link)
                    ],
                );
                ?>

                <?php
                $table->render();
                ?>
            </main>
        </div>
    </div>

    <!-- Modals -->
    <?php
    renderModal(
        'createCategory',
        "fa-solid fa-plus",
        'Add New Category',
        'createCategoryContent', // Must match data-modal-target
        'medium',
        true,
        true,
    );

    // Edit Category Modal
    renderModal(
        'editCategoryModal',
        "fas fa-edit",
        'Edit Category',
        'editCategoryContent',
        'medium',
        true,
        'edit-Category-modal', // additional classes
    );

    renderDeleteModal(
        'deleteConfirmModal',
        'Delete Category',
        'This will permanently delete the Category. Are you sure?',
        'Confirm Delete',
        'Cancel'
    );

    // For AJAX operations
    renderLoadingModal('ajaxLoadingModal');
    ?>

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
    <script src="../js/pagination.js"></script>
</body>

</html>