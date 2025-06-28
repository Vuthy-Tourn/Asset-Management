<?php
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

$categories = $conn->query("SELECT id, name FROM category")->fetch_all(MYSQLI_ASSOC);
$floors = $conn->query("SELECT id, name FROM floor")->fetch_all(MYSQLI_ASSOC);

$categoryOptions = ['' => 'All Categories'] + array_column($categories, 'name', 'id');
$floorOptions = ['' => 'All Floors'] + array_column($floors, 'name', 'id');

$table = new DataTable($conn, [
    'table' => 'products',
    'primaryKey' => 'id',
    'columns' => [
        ['name' => 'id', 'label' => 'ID', 'nowrap' => true],
        [
            'name' => 'name',
            'label' => 'Product Info',
            'format' => 'custom',
            'callback' => function ($row) {
                return '
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-12 w-12">
                            <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-[#0345e4] via-[#026af2] to-[#00279c] flex items-center justify-center">
                                <i class="fas fa-box-open text-white text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">
                                ' . htmlspecialchars($row['name']) . '
                            </div>
                            <div class="text-sm text-gray-500">
                                Code: ' . htmlspecialchars($row['code']) . '
                            </div>
                        </div>
                    </div>
                ';
            }
        ],
        [
            'name' => 'category_name',
            'label' => 'Category',
            'format' => 'badge',
            'colors' => [
                'Desktop' => 'blue',
                'Laptop' => 'purple',
                'Furniture' => 'yellow',
                'Routers & Switches' => 'pink',
                'Printers'=> 'indigo',
                'Air Conditioners' => 'rose',
                'Uncategorized' => 'gray'
            ],
            'default_color' => 'gray'
        ],
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
        ['name' => 'created_at', 'label' => 'Created', 'format' => 'date', 'nowrap' => true],
    ],
    'joins' => [
        'LEFT JOIN category ON products.category_id = category.id',
        'LEFT JOIN floor ON category.floor_id = floor.id'
    ],
    'filterOptions' => [
        'category_id' => $categoryOptions,
        'floor_id' => $floorOptions
    ],
    'dateField' => 'products.created_at',
    'timeFilterOptions' => [
        '' => 'All Time',
        'today' => 'Today',
        'week' => 'This Week',
        'month' => 'This Month',
        'year' => 'This Year',
        'recent' => 'Last 7 Days'
    ],
    'searchable' => ['products.name', 'products.code', 'category.name'],
]);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Asset Management System</title>
    <meta name="description" content="Manage your products efficiently with our comprehensive asset management system">

    <!-- Stylesheets -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
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
                    'Product Management',
                    'Manage your inventory and product information',
                    [
                        'text' => 'Add New Product',
                        'icon' => 'fa-plus',
                        'modalId' => 'createProduct',
                        'modalUrl' => 'create.php',
                        'modalTarget' => 'createProductContent'
                    ],
                    [
                        ['title' => 'Dashboard', 'url' => '/Uni-PHP/Assignment/index.php'],
                        ['title' => 'Products'] // Current page (no link)
                    ],
                );
                ?>

                <!-- Products Table -->
                <div class="rounded-lg shadow-sm overflow-hidden">
                    <?php $table->render(); ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Modals -->
    <?php
    renderModal(
        'createProduct',
        "fa-solid fa-plus",
        'Add New Product',
        'createProductContent', // Must match data-modal-target
        'medium',
        true, // Include default content div
        true, // Include footer with default buttons
    );

    // Edit Product Modal
    renderModal(
        'editProductsModal',
        "fas fa-edit",
        'Edit Product',
        'editProductsContent',
        'medium',
        true,
        'edit-product-modal', // additional classes
    );

    renderDeleteModal(
        'deleteConfirmModal',
        'Delete Product',
        'This will permanently delete the product. Are you sure?',
        'Confirm Delete',
        'Cancel'
    );

    // For AJAX operations
    renderLoadingModal('ajaxLoadingModal');
    ?>

    <!-- Toast Notifications -->
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

    <!-- Scripts -->
    <script src="../js/modal.js"></script>
    <script src="../js/search.js"></script>
    <script src="../js/pagination.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 600,
            once: true
        });
    </script>
</body>

</html>