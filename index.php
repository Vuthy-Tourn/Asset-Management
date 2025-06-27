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
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .animated-gradient {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }

        .card-hover-effect {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover-effect:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .number-counter {
            font-variant-numeric: tabular-nums;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include './components/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Header -->
            <?php include './components/header.php'; ?>

            <!-- Dashboard Content -->
            <main class="p-4 sm:p-6 lg:p-8 space-y-8">
                <!-- Hero Welcome Section -->
                <div class="animate-fade-in">
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-6 lg:p-10 text-white shadow-lg relative overflow-hidden">
                        <!-- Subtle grid pattern background -->
                        <div class="absolute inset-0 opacity-10" style="
            background-image: radial-gradient(circle, currentColor 1px, transparent 1px);
            background-size: 20px 20px;
        "></div>

                        <!-- Animated dots -->
                        <div class="absolute top-0 right-0 w-32 h-32 -mt-16 -mr-16">
                            <div class="absolute top-1/2 left-1/2 w-16 h-16 bg-white/10 rounded-full blur-lg animate-pulse" style="
                animation-delay: 0.5s;
            "></div>
                        </div>

                        <div class="relative z-10 flex flex-col lg:flex-row items-start gap-8">
                            <!-- Main content -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                                    <span class="text-sm font-medium bg-white/10 px-3 py-1 rounded-full">SYSTEM OPERATIONAL</span>
                                </div>

                                <h1 class="text-3xl lg:text-4xl xl:text-5xl font-bold mb-4 leading-tight">
                                    Welcome back, <span class="text-indigo-200">Admin</span> 👋
                                </h1>

                                <p class="text-lg text-white/90 mb-6 max-w-2xl">
                                    Your complete asset management dashboard with real-time monitoring and analytics.
                                </p>

                                <!-- Stats badges -->
                                <div class="flex flex-wrap gap-3">
                                    <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-lg border border-white/20">
                                        <i class="fas fa-clock text-indigo-200"></i>
                                        <span class="text-sm"><?= date('M d, Y') ?></span>
                                    </div>
                                    <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-lg border border-white/20">
                                        <i class="fas fa-database text-indigo-200"></i>
                                        <span class="text-sm">Live Data Sync</span>
                                    </div>
                                    <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-lg border border-white/20">
                                        <i class="fas fa-bolt text-indigo-200"></i>
                                        <span class="text-sm">Real-time Updates</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Status card -->
                            <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl p-5 lg:p-6 w-full lg:w-auto lg:min-w-[280px]">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="font-semibold text-white/90">System Status</h3>
                                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <p class="text-xs text-white/70 mb-1">Last Updated</p>
                                        <p class="font-medium"><?php
                                                                date_default_timezone_set('Asia/Phnom_Penh');
                                                                echo date('g:i A'); // Will now show Cambodia time
                                                                ?></p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-white/70 mb-1">Active Modules</p>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="text-xs bg-white/20 px-2 py-1 rounded">Floors</span>
                                            <span class="text-xs bg-white/20 px-2 py-1 rounded">Categories</span>
                                            <span class="text-xs bg-white/20 px-2 py-1 rounded">Products</span>
                                        </div>
                                    </div>

                                    <div class="pt-4 border-t border-white/10">
                                        <button class="w-full flex items-center justify-center gap-2 bg-white text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors" onclick="refreshActivity()">
                                            <i class=" fas fa-sync-alt"></i>
                                            Refresh Data
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-3 gap-8 animate-slide-up">
                    <!-- Floors Card -->
                    <div class="group card-hover-effect bg-white/80 backdrop-blur-lg rounded-3xl shadow-xl p-8 border border-white/30 relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-blue-600/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-6">
                                <div class="p-4 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg group-hover:animate-bounce-subtle">
                                    <i class="fas fa-building text-2xl"></i>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Total Floors</p>
                                    <h3 class="text-4xl font-black text-slate-800 number-counter"><?= $floors ?></h3>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                    <span class="text-sm font-semibold text-green-600">All Active</span>
                                </div>
                                <a href="floor/read.php" class="group/link inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-bold transition-all duration-300 transform group-hover:translate-x-2">
                                    Manage <i class="fas fa-arrow-right ml-2 text-xs group-hover/link:translate-x-1 transition-transform"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Categories Card -->
                    <div class="group card-hover-effect bg-white/80 backdrop-blur-lg rounded-3xl shadow-xl p-8 border border-white/30 relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-green-600/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-6">
                                <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg group-hover:animate-bounce-subtle">
                                    <i class="fas fa-tags text-2xl"></i>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Categories</p>
                                    <h3 class="text-4xl font-black text-slate-800 number-counter"><?= $categories ?></h3>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                    <span class="text-sm font-semibold text-emerald-600">Growing</span>
                                </div>
                                <a href="category/read.php" class="group/link inline-flex items-center text-emerald-600 hover:text-emerald-800 text-sm font-bold transition-all duration-300 transform group-hover:translate-x-2">
                                    Manage <i class="fas fa-arrow-right ml-2 text-xs group-hover/link:translate-x-1 transition-transform"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Products Card -->
                    <div class="group card-hover-effect bg-white/80 backdrop-blur-lg rounded-3xl shadow-xl p-8 border border-white/30 relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-violet-600/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-6">
                                <div class="p-4 rounded-2xl bg-gradient-to-br from-purple-500 to-violet-600 text-white shadow-lg group-hover:animate-bounce-subtle">
                                    <i class="fas fa-boxes text-2xl"></i>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Products</p>
                                    <h3 class="text-4xl font-black text-slate-800 number-counter"><?= $products ?></h3>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-purple-500 rounded-full animate-pulse"></div>
                                    <span class="text-sm font-semibold text-purple-600">Trending</span>
                                </div>
                                <a href="products/read.php" class="group/link inline-flex items-center text-purple-600 hover:text-purple-800 text-sm font-bold transition-all duration-300 transform group-hover:translate-x-2">
                                    Manage <i class="fas fa-arrow-right ml-2 text-xs group-hover/link:translate-x-1 transition-transform"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Charts Section -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                    <!-- Category Chart -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-xl p-8 lg:p-10 border border-white/30 hover:shadow-2xl transition-all duration-500 relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-pink-500/5 to-rose-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-8">
                                <div>
                                    <h2 class="text-2xl lg:text-3xl font-bold text-slate-800 mb-2">Products by Category</h2>
                                    <p class="text-slate-600 text-sm">Distribution across different categories</p>
                                </div>
                                <div class="p-3 rounded-2xl bg-gradient-to-br from-pink-500 to-rose-500 text-white shadow-lg">
                                    <i class="fas fa-chart-pie text-xl"></i>
                                </div>
                            </div>
                            <div class="relative min-h-[400px]">
                                <canvas id="categoryChart"
                                    data-labels='<?= json_encode(array_column($categoriesData, 'name')) ?>'
                                    data-values='<?= json_encode(array_column($categoriesData, 'product_count')) ?>'></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Floor Chart -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-xl p-8 lg:p-10 border border-white/30 hover:shadow-2xl transition-all duration-500 relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/5 to-blue-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-8">
                                <div>
                                    <h2 class="text-2xl lg:text-3xl font-bold text-slate-800 mb-2">Categories by Floor</h2>
                                    <p class="text-slate-600 text-sm">Category distribution per floor</p>
                                </div>
                                <div class="p-3 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-500 text-white shadow-lg">
                                    <i class="fas fa-chart-bar text-xl"></i>
                                </div>
                            </div>
                            <div class="relative min-h-[400px]">
                                <canvas id="floorChart"
                                    data-labels='<?= json_encode(array_column($floorsData, 'name')) ?>'
                                    data-values='<?= json_encode(array_column($floorsData, 'category_count')) ?>'></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>


    <script src="./js/index.js"></script>
</body>

</html>