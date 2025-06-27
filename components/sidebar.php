<?php
// includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Define navigation items
$nav_items = [
    [
        'title' => 'Dashboard',
        'subtitle' => 'Overview & Analytics',
        'icon' => 'fas fa-tachometer-alt',
        'url' => '/Uni-PHP/Assignment/index.php',
        'active' => $current_page == 'index.php' && $current_dir != 'floor' && $current_dir != 'category' && $current_dir != 'products',
        'color' => 'blue'
    ],
    [
        'title' => 'Floors',
        'subtitle' => 'Building Management',
        'icon' => 'fas fa-building',
        'url' => '/Uni-PHP/Assignment/floor/read.php',
        'active' => strpos($_SERVER['PHP_SELF'], 'floor/') !== false,
        'color' => 'emerald'
    ],
    [
        'title' => 'Categories',
        'subtitle' => 'Item Classification',
        'icon' => 'fas fa-tags',
        'url' => '/Uni-PHP/Assignment/category/read.php',
        'active' => strpos($_SERVER['PHP_SELF'], 'category/') !== false,
        'color' => 'purple'
    ],
    [
        'title' => 'Products',
        'subtitle' => 'Asset Inventory',
        'icon' => 'fas fa-boxes',
        'url' => '/Uni-PHP/Assignment/products/read.php',
        'active' => strpos($_SERVER['PHP_SELF'], 'products/') !== false,
        'color' => 'orange'
    ]
];

$additional_nav_items = [
    [
        'title' => 'Activities',
        'subtitle' => 'Recently Activity',
        'icon' => 'fa-solid fa-chart-line',
        'url' => '/Uni-PHP/Assignment/pages/recent_data.php',
        'active' => strpos($_SERVER['PHP_SELF'], 'recent-data/') !== false,
        'color' => 'yellow'
    ],
    [
        'title' => 'Analytics',
        'subtitle' => 'Data Insights',
        'icon' => 'fas fa-chart-bar',
        'url' => '/Uni-PHP/Assignment/analytics/index.php',
        'active' => strpos($_SERVER['PHP_SELF'], 'analytics/') !== false,
        'color' => 'teal'
    ],
    [
        'title' => 'Reports',
        'subtitle' => 'Export & Print',
        'icon' => 'fas fa-file-alt',
        'url' => '/Uni-PHP/Assignment/reports/index.php',
        'active' => strpos($_SERVER['PHP_SELF'], 'reports/') !== false,
        'color' => 'indigo'
    ],
    // [
    //     'title' => 'Settings',
    //     'subtitle' => 'System Config',
    //     'icon' => 'fas fa-cog',
    //     'url' => '/Uni-PHP/Assignment/settings/index.php',
    //     'active' => strpos($_SERVER['PHP_SELF'], 'settings/') !== false,
    //     'color' => 'gray'
    // ]
];
?>

<style>
    .sidebar-gradient {
        background: linear-gradient(135deg, #1e293b 0%, #334155 25%, #475569 50%, #64748b 75%, #94a3b8 100%);
    }

    .sidebar-gradient::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 51, 234, 0.1) 100%);
        pointer-events: none;
    }

    .nav-item {
        position: relative;
        overflow: hidden;
    }

    .nav-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transition: left 0.5s;
    }

    .nav-item:hover::before {
        left: 100%;
    }

    .smooth-transition {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hover-lift:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .active-indicator {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
        border-left: 4px solid #ffffff;
    }

    .mobile-menu-enter {
        animation: slideIn 0.3s ease-out;
    }

    .mobile-menu-exit {
        animation: slideOut 0.3s ease-in;
    }

    @keyframes slideIn {
        from {
            transform: translateX(-100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(-100%);
            opacity: 0;
        }
    }

    .sidebar-brand-hover:hover {
        transform: scale(1.05);
    }

    .nav-chevron {
        transition: transform 0.2s ease;
    }

    .nav-item:hover .nav-chevron {
        transform: translateX(4px);
    }

    .sidebar-footer-pattern {
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='m36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .sidebar-scrollbar::-webkit-scrollbar {
        width: 4px;
    }

    .sidebar-scrollbar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 2px;
    }

    .sidebar-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 2px;
    }

    .sidebar-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }

    .glass-effect {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .notification-badge {
        animation: pulse-glow 2s infinite;
    }

    @keyframes pulse-glow {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 0 5px rgba(34, 197, 94, 0.5);
        }
        50% {
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(34, 197, 94, 0.8);
        }
    }

    .mobile-overlay {
        backdrop-filter: blur(4px);
        background: rgba(0, 0, 0, 0.5);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .sidebar-mobile-hidden {
            transform: translateX(-100%);
        }
        
        .sidebar-mobile-shown {
            transform: translateX(0);
        }
    }

    @media (max-width: 640px) {
        .sidebar-gradient {
            width: 280px !important;
        }
    }
</style>

<!-- Enhanced Responsive Sidebar -->
<div id="sidebar" class="w-64 lg:w-72 sidebar-gradient text-white flex flex-col h-screen fixed md:static left-0 top-0 z-50 smooth-transition transform -translate-x-full md:translate-x-0 shadow-2xl">

    <!-- Enhanced Logo/Brand Section -->
    <div class="relative p-4 lg:p-6 pb-4 flex items-center justify-between border-b border-white/20">
        <div class="flex items-center space-x-3 lg:space-x-4 sidebar-brand-hover smooth-transition">
            <div class="relative">
                <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-gradient-to-br from-blue-400 via-purple-500 to-indigo-600 flex items-center justify-center shadow-xl smooth-transition">
                    <i class="fas fa-cube text-white text-lg lg:text-xl"></i>
                </div>
                <div class="absolute -top-1 -right-1 w-3 h-3 lg:w-4 lg:h-4 bg-green-400 rounded-full border-2 border-slate-800 notification-badge"></div>
            </div>
            <div class="hidden sm:block">
                <h1 class="text-lg lg:text-xl font-bold tracking-tight bg-gradient-to-r from-white to-blue-200 bg-clip-text text-transparent">
                    Asset Manager
                </h1>
                <p class="text-xs text-blue-300/70 font-medium">Management System v2.0</p>
            </div>
        </div>
        <button id="sidebar-close-button" class="md:hidden p-2 rounded-xl text-blue-300 hover:text-white hover:bg-white/10 smooth-transition hover-lift">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    <!-- Enhanced Navigation Menu -->
    <nav class="flex-1 overflow-y-auto px-3 lg:px-4 py-4 lg:py-6 space-y-2 sidebar-scrollbar">

        <!-- Main Navigation -->
        <div class="space-y-1">
            <h2 class="px-3 text-xs font-semibold text-blue-300/70 uppercase tracking-wider mb-3">Main Menu</h2>

            <?php foreach ($nav_items as $item): ?>
                <div class="nav-item">
                    <a href="<?= $item['url'] ?>"
                        class="flex items-center p-2 lg:p-3 rounded-xl group smooth-transition hover-lift <?= $item['active'] ? 'active-indicator' : 'hover:bg-white/10' ?>">

                        <div class="relative w-9 h-9 lg:w-11 lg:h-11 rounded-lg flex items-center justify-center mr-3 lg:mr-4 smooth-transition 
                               <?= $item['active'] ? 'bg-white/20 shadow-inner' : 'bg-white/10 group-hover:bg-' . $item['color'] . '-500' ?>">
                            <i class="<?= $item['icon'] ?> text-base lg:text-lg 
                                 <?= $item['active'] ? 'text-white' : 'text-blue-300 group-hover:text-white' ?>"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-sm truncate <?= $item['active'] ? 'text-white' : 'text-blue-100' ?>">
                                <?= $item['title'] ?>
                            </div>
                            <div class="text-xs truncate <?= $item['active'] ? 'text-blue-200' : 'text-blue-300/70' ?> hidden lg:block">
                                <?= $item['subtitle'] ?>
                            </div>
                        </div>

                        <?php if ($item['active']): ?>
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 rounded-full bg-white animate-pulse"></div>
                                <i class="fas fa-chevron-right text-white text-xs"></i>
                            </div>
                        <?php else: ?>
                            <i class="fas fa-chevron-right text-blue-400 text-xs opacity-0 group-hover:opacity-100 nav-chevron"></i>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Divider -->
        <div class="my-4 lg:my-6 border-t border-white/20"></div>

        <!-- Additional Navigation -->
        <div class="space-y-1">
            <h2 class="px-3 text-xs font-semibold text-blue-300/70 uppercase tracking-wider mb-3">Tools & Settings</h2>

            <?php foreach ($additional_nav_items as $item): ?>
                <div class="nav-item">
                    <a href="<?= $item['url'] ?>"
                        class="flex items-center p-2 lg:p-3 rounded-xl group smooth-transition hover-lift <?= $item['active'] ? 'active-indicator' : 'hover:bg-white/10' ?>">

                        <div class="relative w-9 h-9 lg:w-11 lg:h-11 rounded-lg flex items-center justify-center mr-3 lg:mr-4 smooth-transition 
                               <?= $item['active'] ? 'bg-white/20 shadow-inner' : 'bg-white/10 group-hover:bg-' . $item['color'] . '-500' ?>">
                            <i class="<?= $item['icon'] ?> text-base lg:text-lg 
                                 <?= $item['active'] ? 'text-white' : 'text-blue-300 group-hover:text-white' ?>"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-sm truncate <?= $item['active'] ? 'text-white' : 'text-blue-100' ?>">
                                <?= $item['title'] ?>
                            </div>
                            <div class="text-xs truncate <?= $item['active'] ? 'text-blue-200' : 'text-blue-300/70' ?> hidden lg:block">
                                <?= $item['subtitle'] ?>
                            </div>
                        </div>

                        <?php if ($item['active']): ?>
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 rounded-full bg-white animate-pulse"></div>
                                <i class="fas fa-chevron-right text-white text-xs"></i>
                            </div>
                        <?php else: ?>
                            <i class="fas fa-chevron-right text-blue-400 text-xs opacity-0 group-hover:opacity-100 nav-chevron"></i>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </nav>

    <!-- Enhanced Footer Section -->
    <div class="relative px-3 lg:px-4 py-3 lg:py-4 border-t border-white/20 sidebar-footer-pattern">
        <div class=" rounded-xl p-3 lg:p-4">
            <div class="flex items-center justify-between text-sm">
                <div class="flex items-center space-x-2 lg:space-x-3">
                    <div class="w-6 h-6 lg:w-8 lg:h-8 rounded-lg bg-gradient-to-r from-green-400 to-emerald-500 flex items-center justify-center">
                        <i class="fas fa-info-circle text-white text-xs lg:text-sm"></i>
                    </div>
                    <div>
                        <div class="text-white font-medium text-xs lg:text-sm">System Status</div>
                        <div class="text-blue-300/70 text-xs">Online • v2.0.0</div>
                    </div>
                </div>
                <div class="flex items-center space-x-1 lg:space-x-2">
                    <button class="p-1.5 lg:p-2 rounded-lg hover:bg-white/10 smooth-transition hover-lift" title="Notifications">
                        <i class="fas fa-bell text-blue-300 hover:text-white text-xs lg:text-sm"></i>
                        <span class="sr-only">Notifications</span>
                    </button>
                    <button class="p-1.5 lg:p-2 rounded-lg hover:bg-white/10 smooth-transition hover-lift" title="Settings">
                        <i class="fas fa-cog text-blue-300 hover:text-white text-xs lg:text-sm"></i>
                        <span class="sr-only">Settings</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Overlay -->
<div class="fixed inset-0 mobile-overlay z-40 md:hidden hidden" id="sidebar-overlay"></div>

<!-- JavaScript for Sidebar -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const sidebarCloseButton = document.getElementById('sidebar-close-button');
    const overlay = document.getElementById('sidebar-overlay');
    let sidebarOpen = false;

    function toggleSidebar() {
        sidebarOpen = !sidebarOpen;
        if (sidebarOpen) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('mobile-menu-enter');
            overlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            setTimeout(() => {
                sidebar.classList.remove('mobile-menu-enter');
            }, 300);
        } else {
            sidebar.classList.add('mobile-menu-exit');
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');

            setTimeout(() => {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('mobile-menu-exit');
            }, 300);
        }
    }

    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', toggleSidebar);
    }

    if (sidebarCloseButton) {
        sidebarCloseButton.addEventListener('click', toggleSidebar);
    }

    if (overlay) {
        overlay.addEventListener('click', toggleSidebar);
    }

    function handleResize() {
        if (window.innerWidth >= 768) {
            sidebar.classList.remove('-translate-x-full', 'mobile-menu-enter', 'mobile-menu-exit');
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            sidebarOpen = false;
        } else if (!sidebarOpen) {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }

    handleResize();
    window.addEventListener('resize', handleResize);

    // Smooth scroll for navigation links
    document.querySelectorAll('.nav-item a').forEach(link => {
        link.addEventListener('click', function(e) {
            const icon = this.querySelector('i');
            const originalClass = icon.className;
            icon.className = 'fas fa-spinner fa-spin text-lg';

            setTimeout(() => {
                icon.className = originalClass;
            }, 500);
        });
    });

    // Keyboard navigation support
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebarOpen && window.innerWidth < 768) {
            toggleSidebar();
        }
        if (e.ctrlKey && e.key === 'b' && window.innerWidth < 768) {
            e.preventDefault();
            toggleSidebar();
        }
    });

    // Focus trap for accessibility
    const focusableElements = sidebar.querySelectorAll('button, a, input, select, textarea, [tabindex]:not([tabindex="-1"])');
    const firstFocusableElement = focusableElements[0];
    const lastFocusableElement = focusableElements[focusableElements.length - 1];

    sidebar.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            if (e.shiftKey) {
                if (document.activeElement === firstFocusableElement) {
                    lastFocusableElement.focus();
                    e.preventDefault();
                }
            } else {
                if (document.activeElement === lastFocusableElement) {
                    firstFocusableElement.focus();
                    e.preventDefault();
                }
            }
        }
    });
});
</script>