<?php
require_once './components/config/db.php';

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'bounce-subtle': 'bounceSubtle 2s infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': {
                                opacity: '0',
                                transform: 'translateY(10px)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateY(0)'
                            }
                        },
                        slideUp: {
                            '0%': {
                                opacity: '0',
                                transform: 'translateY(20px)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateY(0)'
                            }
                        },
                        bounceSubtle: {
                            '0%, 100%': {
                                transform: 'translateY(0)'
                            },
                            '50%': {
                                transform: 'translateY(-2px)'
                            }
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include './components/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Header -->
            <?php include './components/header.php'; ?>

            <!-- Dashboard Content -->
            <main class="p-4 sm:p-6 lg:p-8 space-y-8">
                <!-- Welcome Section -->
                <div class="animate-fade-in">
                    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 rounded-2xl p-6 lg:p-8 text-white shadow-xl">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                            <div>
                                <h1 class="text-2xl lg:text-3xl font-bold mb-2">Welcome back! 👋</h1>
                                <p class="text-blue-100 text-sm lg:text-base">Here's what's happening with your assets today.</p>
                            </div>
                            <div class="mt-4 sm:mt-0">
                                <div class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                                    <p class="text-xs text-blue-100">Last updated</p>
                                    <p class="text-sm font-semibold"><?= date('M d, Y H:i') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-slide-up">
                    <!-- Floors Card -->
                    <div class="group bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-white/20 hover:scale-[1.02]">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg group-hover:animate-bounce-subtle">
                                <i class="fas fa-building text-xl"></i>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Floors</p>
                                <h3 class="text-3xl font-bold text-slate-800 mt-1"><?= $floors ?></h3>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-1 text-green-600">
                                <i class="fas fa-arrow-up text-xs"></i>
                                <span class="text-sm font-semibold">Active</span>
                            </div>
                            <a href="floor/read.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors duration-200 group-hover:translate-x-1">
                                View all <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Categories Card -->
                    <div class="group bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-white/20 hover:scale-[1.02]">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg group-hover:animate-bounce-subtle">
                                <i class="fas fa-tags text-xl"></i>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Categories</p>
                                <h3 class="text-3xl font-bold text-slate-800 mt-1"><?= $categories ?></h3>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-1 text-green-600">
                                <i class="fas fa-arrow-up text-xs"></i>
                                <span class="text-sm font-semibold">Growing</span>
                            </div>
                            <a href="category/read.php" class="inline-flex items-center text-emerald-600 hover:text-emerald-800 text-sm font-medium transition-colors duration-200 group-hover:translate-x-1">
                                View all <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Products Card -->
                    <div class="group bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-white/20 hover:scale-[1.02]">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 rounded-xl bg-gradient-to-br from-purple-500 to-violet-600 text-white shadow-lg group-hover:animate-bounce-subtle">
                                <i class="fas fa-boxes text-xl"></i>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Products</p>
                                <h3 class="text-3xl font-bold text-slate-800 mt-1"><?= $products ?></h3>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-1 text-green-600">
                                <i class="fas fa-arrow-up text-xs"></i>
                                <span class="text-sm font-semibold">Trending</span>
                            </div>
                            <a href="products/read.php" class="inline-flex items-center text-purple-600 hover:text-purple-800 text-sm font-medium transition-colors duration-200 group-hover:translate-x-1">
                                View all <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                    <!-- Category Chart -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg p-6 lg:p-8 border border-white/20 hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-xl lg:text-2xl font-bold text-slate-800">Products by Category</h2>
                                <p class="text-slate-500 text-sm mt-1">Distribution across different categories</p>
                            </div>
                            <div class="p-2 rounded-lg bg-gradient-to-br from-pink-500 to-rose-500 text-white">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                        </div>
                        <div class="relative min-h-[350px]">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>

                    <!-- Floor Chart -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg p-6 lg:p-8 border border-white/20 hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-xl lg:text-2xl font-bold text-slate-800">Categories by Floor</h2>
                                <p class="text-slate-500 text-sm mt-1">Category distribution per floor</p>
                            </div>
                            <div class="p-2 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-500 text-white">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                        </div>
                        <div class="relative min-h-[350px]">
                            <canvas id="floorChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg p-6 lg:p-8 border border-white/20">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl lg:text-2xl font-bold text-slate-800">Recent Activity</h2>
                            <p class="text-slate-500 text-sm mt-1">Latest updates and changes</p>
                        </div>
                        <div class="p-2 rounded-lg bg-gradient-to-br from-orange-500 to-amber-500 text-white">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <!-- Activity Item 1 -->
                        <div class="group flex items-start p-4 rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 hover:shadow-md transition-all duration-300">
                            <div class="flex-shrink-0 p-3 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-plus text-sm"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-slate-800">New floor added</p>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">New</span>
                                </div>
                                <p class="text-sm text-slate-600 mt-1">Floor "Building A - Level 3" has been successfully created</p>
                                <p class="text-xs text-slate-500 mt-2 flex items-center">
                                    <i class="fas fa-clock mr-1"></i>
                                    2 hours ago
                                </p>
                            </div>
                        </div>

                        <!-- Activity Item 2 -->
                        <div class="group flex items-start p-4 rounded-xl bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-100 hover:shadow-md transition-all duration-300">
                            <div class="flex-shrink-0 p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-edit text-sm"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-slate-800">Category updated</p>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Updated</span>
                                </div>
                                <p class="text-sm text-slate-600 mt-1">Electronics category details have been modified</p>
                                <p class="text-xs text-slate-500 mt-2 flex items-center">
                                    <i class="fas fa-clock mr-1"></i>
                                    5 hours ago
                                </p>
                            </div>
                        </div>

                        <!-- Activity Item 3 -->
                        <div class="group flex items-start p-4 rounded-xl bg-gradient-to-r from-purple-50 to-violet-50 border border-purple-100 hover:shadow-md transition-all duration-300">
                            <div class="flex-shrink-0 p-3 rounded-xl bg-gradient-to-br from-purple-500 to-violet-600 text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-box text-sm"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-slate-800">New products registered</p>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Added</span>
                                </div>
                                <p class="text-sm text-slate-600 mt-1">5 new items have been added to inventory</p>
                                <p class="text-xs text-slate-500 mt-2 flex items-center">
                                    <i class="fas fa-clock mr-1"></i>
                                    1 day ago
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- View All Activity Button -->
                    <div class="mt-6 text-center">
                        <button class="inline-flex items-center px-6 py-3 rounded-xl bg-gradient-to-r from-slate-600 to-slate-700 text-white font-medium hover:from-slate-700 hover:to-slate-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="fas fa-history mr-2"></i>
                            View All Activity
                        </button>
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

            // Modern color palette
            const modernColors = [
                '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#84CC16'
            ];

            // Category Chart (Doughnut)
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        data: categoryData,
                        backgroundColor: modernColors,
                        borderWidth: 0,
                        hoverBorderWidth: 3,
                        hoverBorderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    size: 12,
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    const percentage = ((context.raw / categoryData.reduce((a, b) => a + b, 0)) * 100).toFixed(1);
                                    return `${context.label}: ${context.raw} products (${percentage}%)`;
                                }
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        duration: 2000
                    }
                }
            });

            // Floor Chart (Bar)
            const floorCtx = document.getElementById('floorChart').getContext('2d');
            new Chart(floorCtx, {
                type: 'bar',
                data: {
                    labels: floorLabels,
                    datasets: [{
                        label: 'Categories',
                        data: floorData,
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
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
                                stepSize: 1,
                                font: {
                                    size: 12,
                                    weight: '500'
                                },
                                color: '#64748B'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 12,
                                    weight: '500'
                                },
                                color: '#64748B'
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return `${context.raw} categories`;
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeOutCubic'
                    }
                }
            });

            // Add some interactive animations
            const cards = document.querySelectorAll('.group');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>

</html>