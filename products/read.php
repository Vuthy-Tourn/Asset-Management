<?php
require_once '../components/config/db.php';
require_once '../components/flash.php';

session_start();

// Display flash message if exists
$flash = flash();
if ($flash) {
    require_once '../components/toast.php';
    showToast($flash['message'], $flash['type']);
}

// Input validation and sanitization
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Search functionality
$search = $_GET['search'] ?? '';
$searchCondition = '';
$searchParams = [];

if (!empty($search)) {
    $searchCondition = "WHERE (products.name LIKE ? OR products.code LIKE ? OR category.name LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $searchParams = [$searchTerm, $searchTerm, $searchTerm];
}

try {
    // Get total count with search
    $countSql = "SELECT COUNT(*) FROM products 
                 LEFT JOIN category ON products.category_id = category.id 
                 LEFT JOIN floor ON category.floor_id = floor.id 
                 $searchCondition";

    $countStmt = $conn->prepare($countSql);
    if (!empty($searchParams)) {
        $countStmt->bind_param(str_repeat('s', count($searchParams)), ...$searchParams);
    }
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_row()[0];
    $totalPages = ceil($total / $limit);

    // Get products with pagination and search
    $sql = "SELECT products.*, category.name as category_name, floor.name as floor_name 
            FROM products 
            LEFT JOIN category ON products.category_id = category.id 
            LEFT JOIN floor ON category.floor_id = floor.id 
            $searchCondition
            ORDER BY products.created_at  
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    $params = array_merge($searchParams, [$limit, $offset]);
    $types = str_repeat('s', count($searchParams)) . 'ii';
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $products = [];
    $total = 0;
    $totalPages = 0;
}
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
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Product Management</h1>
                        <p class="text-gray-600 mt-1">Manage your inventory and product information</p>
                    </div>
                    <button
                        id="addProductBtn"
                        data-modal-fetch="createProductModal"
                        data-modal-url="create.php"
                        data-modal-target="createProductContent"
                        class="bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-6 py-3 rounded-lg flex items-center transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="fas fa-plus mr-2"></i> Add New Product
                    </button>
                </div>

                <!-- Search and Filters -->
                <div class="rounded-lg shadow-sm p-4 mb-6">
                    <form id="searchForm" class="flex flex-col sm:flex-row gap-4 items-center">
                        <div class="relative flex-1 max-w-md">
                            <input
                                type="text"
                                id="searchInput"
                                name="search"
                                value="<?= htmlspecialchars($search) ?>"
                                placeholder="Search products, codes, or categories..."
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200">
                            <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
                        </div>
                    </form>
                </div>

                <!-- Products Table -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <?php if (empty($products)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-600 mb-2">
                                <?= !empty($search) ? 'No products found' : 'No products yet' ?>
                            </h3>
                            <p class="text-gray-500 mb-6">
                                <?= !empty($search) ? 'Try adjusting your search terms' : 'Start by adding your first product' ?>
                            </p>
                            <?php if (empty($search)): ?>
                                <button
                                    data-modal-fetch="createProductModal"
                                    data-modal-url="create.php"
                                    data-modal-target="createProductContent"
                                    class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg">
                                    <i class="fas fa-plus mr-2"></i>Add Product
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <span>Product Info</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Floor</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($products as $product): ?>
                                        <tr class="table-row transition-colors duration-150" data-product-id="<?= $product['id'] ?>">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-12 w-12">
                                                        <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center">
                                                            <i class="fas fa-box text-white text-lg"></i>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <?= htmlspecialchars($product['name']) ?>
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            Code: <?= htmlspecialchars($product['code']) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?= htmlspecialchars($product['floor_name'] ?? 'N/A') ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?= date('M d, Y', strtotime($product['created_at'])) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center space-x-3">
                                                    <button
                                                        class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                                        data-modal-fetch="editProductModal"
                                                        data-modal-url="update.php?id=<?= $product['id'] ?>"
                                                        data-modal-target="editProductContent"
                                                        title="Edit Product">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button
                                                        class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                                        data-modal-delete="deleteConfirmModal"
                                                        data-modal-url="delete.php?id=<?= $product['id'] ?>"
                                                        title="Delete Product">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                                <div class="flex-1 flex justify-between sm:hidden">
                                    <?php if ($page > 1): ?>
                                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                            Previous
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($page < $totalPages): ?>
                                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                            Next
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Showing <span class="font-medium"><?= $offset + 1 ?></span> to
                                            <span class="font-medium"><?= min($offset + $limit, $total) ?></span> of
                                            <span class="font-medium"><?= $total ?></span> results
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                            <?php if ($page > 1): ?>
                                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php
                                            $start = max(1, $page - 2);
                                            $end = min($totalPages, $page + 2);

                                            for ($i = $start; $i <= $end; $i++): ?>
                                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?= $i == $page ? 'z-10 bg-purple-50 border-purple-500 text-purple-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?>">
                                                    <?= $i ?>
                                                </a>
                                            <?php endfor; ?>

                                            <?php if ($page < $totalPages): ?>
                                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            <?php endif; ?>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Create Product Modal -->
    <div id="createProductModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center">
        <div class="modal-content bg-white p-6 rounded-lg max-w-2xl w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">Add New Product</h3>
                <button data-modal-close="createProductModal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    &times;
                </button>
            </div>
            <div id="createProductContent">
                <!-- Content will be loaded here from create.php -->
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center">
        <div class="modal-content bg-white p-6 rounded-lg max-w-2xl w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">Edit Product</h3>
                <button data-modal-close="editProductModal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    &times;
                </button>
            </div>
            <div id="editProductContent">
                <!-- Content will be loaded here from update.php -->
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center">
        <div class="modal-content bg-white p-6 rounded-lg max-w-md w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">Confirm Deletion</h3>
                <button data-modal-close="deleteConfirmModal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    &times;
                </button>
            </div>
            <div class="mb-6">
                <p class="text-gray-700">Are you sure you want to delete this product? This action cannot be undone.</p>
            </div>
            <div class="flex justify-end space-x-3">
                <button data-modal-close="deleteConfirmModal" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button data-modal-confirm-delete="deleteConfirmModal" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Delete
                </button>
            </div>
        </div>
    </div>

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
            // Auto-hide toast after 5 seconds
            setTimeout(() => {
                const toast = document.getElementById('toast');
                if (toast) toast.remove();
            }, 5000);
        </script>
    <?php endif; ?>

    <!-- Scripts -->
    <script src="../js/modal.js"></script>
    <script src="../js/search.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 600,
            once: true
        });

        // Initialize search functionality
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('searchForm').submit();
            }
        });
    </script>
</body>

</html>