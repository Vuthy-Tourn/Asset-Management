<?php
require_once 'db.php';

// Count totals for dashboard
$floors = $conn->query("SELECT COUNT(*) FROM floor")->fetch_row()[0];
$categories = $conn->query("SELECT COUNT(*) FROM category")->fetch_row()[0];
$products = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];

// Get data for category chart (products per category)
$categoriesData = $conn->query("
    SELECT c.name, COUNT(p.id) as product_count 
    FROM category c
    LEFT JOIN products p ON c.id = p.category_id
    GROUP BY c.id
")->fetch_all(MYSQLI_ASSOC);

// Get data for floor chart (categories per floor)
$floorsData = $conn->query("
    SELECT f.name, COUNT(c.id) as category_count 
    FROM floor f
    LEFT JOIN category c ON f.id = c.floor_id
    GROUP BY f.id
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Management Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include './components/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Header -->
            <?php include './components/header.php'; ?>

            <!-- Dashboard Content -->
            <main class="p-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Total Floors</p>
                                <h3 class="text-2xl font-bold"><?= $floors ?></h3>
                            </div>
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="fas fa-building text-xl"></i>
                            </div>
                        </div>
                        <a href="floor/read.php" class="mt-4 inline-flex items-center text-blue-600 hover:text-blue-800">
                            View all <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Total Categories</p>
                                <h3 class="text-2xl font-bold"><?= $categories ?></h3>
                            </div>
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-tags text-xl"></i>
                            </div>
                        </div>
                        <a href="category/read.php" class="mt-4 inline-flex items-center text-green-600 hover:text-green-800">
                            View all <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Total Products</p>
                                <h3 class="text-2xl font-bold"><?= $products ?></h3>
                            </div>
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <i class="fas fa-boxes text-xl"></i>
                            </div>
                        </div>
                        <a href="products/read.php" class="mt-4 inline-flex items-center text-purple-600 hover:text-purple-800">
                            View all <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Products by Category</h2>
                        <div class="flex-1 min-h-[300px]">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Categories by Floor</h2>
                        <div class="flex-1 min-h-[300px]">
                            <canvas id="floorChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Recent Activity</h2>
                    <div class="space-y-4">
                        <!-- Sample activity items -->
                        <div class="flex items-start border-b pb-4">
                            <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-4">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div>
                                <p class="font-medium">New floor added</p>
                                <p class="text-sm text-gray-500">2 hours ago</p>
                            </div>
                        </div>
                        <div class="flex items-start border-b pb-4">
                            <div class="p-2 rounded-full bg-green-100 text-green-600 mr-4">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div>
                                <p class="font-medium">Category updated</p>
                                <p class="text-sm text-gray-500">5 hours ago</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="p-2 rounded-full bg-purple-100 text-purple-600 mr-4">
                                <i class="fas fa-box"></i>
                            </div>
                            <div>
                                <p class="font-medium">New product registered</p>
                                <p class="text-sm text-gray-500">1 day ago</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Prepare data for charts
            const categoryLabels = <?= json_encode(array_column($categoriesData, 'name')) ?>;
            const categoryData = <?= json_encode(array_column($categoriesData, 'product_count')) ?>;

            const floorLabels = <?= json_encode(array_column($floorsData, 'name')) ?>;
            const floorData = <?= json_encode(array_column($floorsData, 'category_count')) ?>;

            // Category Chart
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        data: categoryData,
                        backgroundColor: [
                            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                            '#EC4899', '#14B8A6', '#F97316', '#64748B', '#84CC16'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw} products`;
                                }
                            }
                        }
                    }
                }
            });

            // Floor Chart
            const floorCtx = document.getElementById('floorChart').getContext('2d');
            new Chart(floorCtx, {
                type: 'bar',
                data: {
                    labels: floorLabels,
                    datasets: [{
                        label: 'Number of Categories',
                        data: floorData,
                        backgroundColor: '#10B981',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.raw} categories`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>