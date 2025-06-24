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

// Get recent activity data (last 10 activities)
$recentActivity = $conn->query("
    (SELECT 'floor' as type, name, created_at, 'added' as action, 'building' as icon FROM floor ORDER BY created_at DESC LIMIT 5)
    UNION ALL
    (SELECT 'category' as type, name, created_at, 'added' as action, 'tags' as icon FROM category ORDER BY created_at DESC LIMIT 5)
    UNION ALL
    (SELECT 'product' as type, name, created_at, 'added' as action, 'box' as icon FROM products ORDER BY created_at DESC LIMIT 5)
    ORDER BY created_at DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Helper function to get activity color and icon
function getActivityStyle($type)
{
    switch ($type) {
        case 'floor':
            return ['color' => 'blue', 'icon' => 'building', 'bg' => 'from-blue-50 to-indigo-50', 'border' => 'border-blue-100', 'badge' => 'bg-blue-100 text-blue-800'];
        case 'category':
            return ['color' => 'emerald', 'icon' => 'tags', 'bg' => 'from-emerald-50 to-green-50', 'border' => 'border-emerald-100', 'badge' => 'bg-emerald-100 text-emerald-800'];
        case 'product':
            return ['color' => 'purple', 'icon' => 'boxes', 'bg' => 'from-purple-50 to-violet-50', 'border' => 'border-purple-100', 'badge' => 'bg-purple-100 text-purple-800'];
        default:
            return ['color' => 'gray', 'icon' => 'circle', 'bg' => 'from-gray-50 to-slate-50', 'border' => 'border-gray-100', 'badge' => 'bg-gray-100 text-gray-800'];
    }
}
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
                    <div class="animated-gradient rounded-3xl p-8 lg:p-12 text-white shadow-2xl relative overflow-hidden">
                        <!-- Floating decorative elements -->
                        <div class="absolute top-4 right-4 w-32 h-32 bg-white/10 rounded-full blur-xl animate-float"></div>
                        <div class="absolute bottom-8 left-8 w-24 h-24 bg-white/5 rounded-full blur-lg animate-float" style="animation-delay: 1s;"></div>

                        <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between">
                            <div class="mb-6 lg:mb-0">
                                <h1 class="text-3xl lg:text-5xl font-bold mb-4 leading-tight">
                                    Welcome back! 👋
                                </h1>
                                <p class="text-white/90 text-lg lg:text-xl max-w-2xl leading-relaxed">
                                    Monitor your asset ecosystem with real-time insights and comprehensive analytics.
                                </p>
                                <div class="flex flex-wrap gap-4 mt-6 text-gray-800">
                                    <div class="glass-effect rounded-xl px-4 py-2">
                                        <span class="text-sm font-medium">📊 Live Data</span>
                                    </div>
                                    <div class="glass-effect rounded-xl px-4 py-2">
                                        <span class="text-sm font-medium">🔄 Auto-Updated</span>
                                    </div>
                                    <div class="glass-effect rounded-xl px-4 py-2">
                                        <span class="text-sm font-medium">⚡ Real-time</span>
                                    </div>
                                </div>
                            </div>
                            <div class="glass-effect rounded-2xl p-6 text-center min-w-[200px] text-gray-800">
                                <p class="text-sm mb-2">System Status</p>
                                <div class="flex items-center justify-center mb-2">
                                    <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse mr-2"></div>
                                    <span class="text-lg font-bold">All Systems Operational</span>
                                </div>
                                <p class="text-sm">Last updated: <?= date('M d, Y H:i') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 animate-slide-up">
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
                                <canvas id="categoryChart"></canvas>
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
                                <canvas id="floorChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Recent Activity -->
                <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-xl p-8 lg:p-10 border border-white/30 relative overflow-hidden">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-2xl lg:text-3xl font-bold text-slate-800 mb-2">Recent Activity</h2>
                            <p class="text-slate-600 text-sm">Latest updates from your system</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="p-3 rounded-2xl bg-gradient-to-br from-orange-500 to-amber-500 text-white shadow-lg">
                                <i class="fas fa-clock text-xl"></i>
                            </div>
                            <button class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition-colors duration-200">
                                <i class="fas fa-sync-alt mr-2"></i>Refresh
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <?php if (empty($recentActivity)): ?>
                            <div class="text-center py-12">
                                <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-inbox text-3xl text-slate-400"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-slate-600 mb-2">No Recent Activity</h3>
                                <p class="text-slate-500">Start adding floors, categories, or products to see activity here.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recentActivity as $index => $activity): ?>
                                <?php $style = getActivityStyle($activity['type']); ?>
                                <div class="group flex items-start p-6 rounded-2xl bg-gradient-to-r <?= $style['bg'] ?> border <?= $style['border'] ?> hover:shadow-lg transition-all duration-300 animate-fade-in" style="animation-delay: <?= $index * 0.1 ?>s;">
                                    <div class="flex-shrink-0 p-4 rounded-2xl bg-gradient-to-br from-<?= $style['color'] ?>-500 to-<?= $style['color'] ?>-600 text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-<?= $style['icon'] ?> text-lg"></i>
                                    </div>
                                    <div class="ml-6 flex-1">
                                        <div class="flex items-center justify-between mb-2">
                                            <p class="font-bold text-slate-800 text-lg">
                                                New <?= ucfirst($activity['type']) ?> <?= $activity['action'] ?>
                                            </p>
                                            <span class="px-3 py-1 rounded-full text-xs font-bold <?= $style['badge'] ?> uppercase tracking-wide">
                                                <?= ucfirst($activity['action']) ?>
                                            </span>
                                        </div>
                                        <p class="text-slate-700 font-medium mb-3">
                                            "<?= htmlspecialchars($activity['name']) ?>" has been successfully <?= $activity['action'] ?>
                                        </p>
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm text-slate-500 flex items-center">
                                                <i class="fas fa-clock mr-2"></i>
                                                <?= timeAgo($activity['created_at']) ?>
                                            </p>
                                            <p class="text-xs text-slate-400">
                                                <?= date('M d, Y \a\t g:i A', strtotime($activity['created_at'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Enhanced View All Activity Button -->
                    <div class="mt-8 text-center">
                        <button class="group inline-flex items-center px-8 py-4 rounded-2xl bg-gradient-to-r from-slate-700 to-slate-800 text-white font-bold hover:from-slate-800 hover:to-slate-900 transition-all duration-300 shadow-xl hover:shadow-2xl transform hover:scale-105">
                            <i class="fas fa-history mr-3 group-hover:animate-spin"></i>
                            View Complete Activity Log
                            <i class="fas fa-arrow-right ml-3 group-hover:translate-x-1 transition-transform"></i>
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

            // Enhanced modern color palette
            const modernColors = [
                '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#84CC16',
                '#06B6D4', '#F43F5E', '#8B5A2B', '#059669', '#7C3AED'
            ];

            // Enhanced Category Chart (Doughnut)
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        data: categoryData,
                        backgroundColor: modernColors,
                        borderWidth: 0,
                        hoverBorderWidth: 4,
                        hoverBorderColor: '#ffffff',
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 25,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    size: 14,
                                    weight: '600'
                                },
                                color: '#374151'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.9)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            cornerRadius: 12,
                            displayColors: true,
                            titleFont: {
                                size: 16,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 14
                            },
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    return `${context.raw} categories`;
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 2500,
                        easing: 'easeOutCubic'
                    }
                }
            });

            // Number counter animation
            function animateCounters() {
                const counters = document.querySelectorAll('.number-counter');
                counters.forEach(counter => {
                    const target = parseInt(counter.textContent);
                    const increment = target / 50;
                    let current = 0;

                    const timer = setInterval(() => {
                        current += increment;
                        counter.textContent = Math.floor(current);

                        if (current >= target) {
                            counter.textContent = target;
                            clearInterval(timer);
                        }
                    }, 30);
                });
            }

            // Intersection Observer for animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe all animated elements
            document.querySelectorAll('.animate-fade-in, .animate-slide-up').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });

            // Start counter animation after a delay
            setTimeout(animateCounters, 1000);

            // Enhanced hover effects for cards
            const cards = document.querySelectorAll('.card-hover-effect');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                    this.style.boxShadow = '0 25px 60px -12px rgba(0, 0, 0, 0.25)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                    this.style.boxShadow = '';
                });
            });

            // Refresh button functionality
            const refreshBtn = document.querySelector('button[class*="refresh"]');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    const icon = this.querySelector('i');
                    icon.classList.add('fa-spin');

                    // Simulate refresh
                    setTimeout(() => {
                        icon.classList.remove('fa-spin');
                        location.reload();
                    }, 1000);
                });
            }

            // Add parallax effect to hero section
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const heroSection = document.querySelector('.animated-gradient');
                if (heroSection) {
                    heroSection.style.transform = `translateY(${scrolled * 0.3}px)`;
                }
            });

            // Add smooth scrolling for internal links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Add loading states for chart containers
            const chartContainers = document.querySelectorAll('canvas');
            chartContainers.forEach(canvas => {
                const container = canvas.parentElement;
                container.style.position = 'relative';

                // Add loading spinner
                const spinner = document.createElement('div');
                spinner.className = 'absolute inset-0 flex items-center justify-center';
                spinner.innerHTML = `
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                `;
                container.appendChild(spinner);

                // Remove spinner after chart loads
                setTimeout(() => {
                    spinner.remove();
                }, 2000);
            });

            // Add real-time clock update
            function updateClock() {
                const clockElements = document.querySelectorAll('[data-clock]');
                const now = new Date();
                const timeString = now.toLocaleString('en-US', {
                    month: 'short',
                    day: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                clockElements.forEach(el => {
                    el.textContent = timeString;
                });
            }

            // Update every minute
            setInterval(updateClock, 60000);

            // Add subtle animations to activity items
            const activityItems = document.querySelectorAll('.group[class*="activity"]');
            activityItems.forEach((item, index) => {
                item.style.animationDelay = `${index * 0.1}s`;

                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(8px)';
                });

                item.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                });
            });

            // Add keyboard navigation support
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Tab') {
                    document.body.classList.add('keyboard-nav');
                }
            });

            document.addEventListener('mousedown', function() {
                document.body.classList.remove('keyboard-nav');
            });

            // Performance optimization: Reduce animations on low-end devices
            const isLowEndDevice = navigator.hardwareConcurrency <= 2;
            if (isLowEndDevice) {
                document.documentElement.style.setProperty('--animation-duration', '0.3s');
            }

            console.log('🚀 Dashboard loaded successfully!');
        });
    </script>
</body>

</html>