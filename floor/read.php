<?php
// read.php
require_once '../components/config/db.php';
require_once '../components/flash.php';
require_once '../components/datatable.php';
require_once '../components/page_header.php';
require_once '../components/modal.php';
session_start();

// Display flash message if exists
$flash = flash();
if ($flash) {
    require_once '../components/toast.php';
    showToast($flash['message'], $flash['type']);
}

$table = new DataTable($conn, [
    'table' => 'floor',
    'primaryKey' => 'id',
    'columns' => [
        ['name' => 'id', 'label' => 'ID', 'nowrap' => true],
        ['name' => 'name', 'label' => 'Name'],
        ['name' => 'code', 'label' => 'Code', 'nowrap' => true],
        ['name' => 'note', 'label' => 'Note', 'format' => 'truncate', 'length' => 50],
        ['name' => 'created_at', 'label' => 'Created At', 'format' => 'date', 'nowrap' => true],
    ],
    'dateField' => 'floor.created_at',
    'searchable' => ['floor.name', 'floor.code', 'floor.note'],
    'addButton' => true,
    'addButtonText' => 'Add Floor',
    'addButtonModal' => 'createFloorModal',
]);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Floors</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include '../components/sidebar.php'; ?>
        <div class="flex-1 overflow-auto">
            <?php include '../components/header.php'; ?>
            <main class="p-6">
                <!-- Page Header -->
                <?php
                renderPageHeader(
                    "Floor Management",
                    "Manage your Floor information",
                    [
                        'text' => 'Add New Floor',
                        'icon' => 'fa-plus',
                        'modalId' => 'createFloor',
                        'modalUrl' => 'create.php',
                        'modalTarget' => 'createFloorContent'
                    ],
                    [
                        ['title' => 'Dashboard', 'url' => '/Uni-PHP/Assignment/index.php'],
                        ['title' => 'Categories'] // Current page (no link)
                    ],
                );
                ?>

                <!-- Table -->
                <div>
                    <?php
                    // Render the table
                    $table->render();
                    ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Modals -->
    <?php
    renderModal(
        'createFloor',
        "fa-solid fa-plus",
        'Add New Floor',
        'createFloorContent', // Must match data-modal-target
        'medium',
        true, // Include default content div
        true, // Include footer with default buttons
    );

    // Edit Floor Modal
    renderModal(
        'editFloorModal',
        "fas fa-edit",
        'Edit Floor',
        'editFloorContent',
        'medium',
        true,
        'edit-floor-modal', // additional classes
    );

    renderDeleteModal(
        'deleteConfirmModal',
        'Delete Floor',
        'This will permanently delete the Floor. Are you sure?',
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