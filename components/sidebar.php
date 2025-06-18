<!-- sidebar.php -->
<div class="w-64 bg-gradient-to-b from-blue-800 to-blue-900 text-white flex flex-col h-screen fixed md:static left-0 top-0 z-40 transition-all duration-300 ease-in-out -translate-x-full md:translate-x-0">
    <!-- Logo/Brand Section -->
    <div class="p-6 pb-4 flex items-center justify-between border-b border-blue-700/50">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                <i class="fas fa-cube text-blue-300"></i>
            </div>
            <span class="text-xl font-bold tracking-tight">Asset Manager</span>
        </div>
        <button id="sidebar-close-button" class="md:hidden text-blue-300 hover:text-white">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto px-3 py-4">
        <ul class="space-y-1">
            <li>
                <a href="/Uni-PHP/Assignment/index.php" 
                   class="flex items-center p-3 rounded-lg group transition-all duration-200
                          <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 
                             'bg-blue-600 shadow-lg' : 'hover:bg-blue-700/50' ?>">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center mr-3
                               <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 
                                  'bg-blue-500' : 'group-hover:bg-blue-500' ?>">
                        <i class="fas fa-tachometer-alt text-sm"></i>
                    </div>
                    <span class="font-medium">Dashboard</span>
                    <?php if(basename($_SERVER['PHP_SELF']) == 'index.php'): ?>
                        <span class="ml-auto w-2 h-2 rounded-full bg-blue-300 animate-pulse"></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <!-- Other menu items same as before -->
            <li>
                <a href="/Uni-PHP/Assignment/floor/read.php" 
                   class="flex items-center p-3 rounded-lg group transition-all duration-200
                          <?= strpos($_SERVER['PHP_SELF'], 'floor/') !== false ? 
                             'bg-blue-600 shadow-lg' : 'hover:bg-blue-700/50' ?>">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center mr-3
                               <?= strpos($_SERVER['PHP_SELF'], 'floor/') !== false ? 
                                  'bg-blue-500' : 'group-hover:bg-blue-500' ?>">
                        <i class="fas fa-building text-sm"></i>
                    </div>
                    <span class="font-medium">Floors</span>
                    <?php if(strpos($_SERVER['PHP_SELF'], 'floor/') !== false): ?>
                        <span class="ml-auto w-2 h-2 rounded-full bg-blue-300 animate-pulse"></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li>
                <a href="/Uni-PHP/Assignment/category/read.php" 
                   class="flex items-center p-3 rounded-lg group transition-all duration-200
                          <?= strpos($_SERVER['PHP_SELF'], 'category/') !== false ? 
                             'bg-blue-600 shadow-lg' : 'hover:bg-blue-700/50' ?>">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center mr-3
                               <?= strpos($_SERVER['PHP_SELF'], 'category/') !== false ? 
                                  'bg-blue-500' : 'group-hover:bg-blue-500' ?>">
                        <i class="fas fa-tags text-sm"></i>
                    </div>
                    <span class="font-medium">Categories</span>
                    <?php if(strpos($_SERVER['PHP_SELF'], 'category/') !== false): ?>
                        <span class="ml-auto w-2 h-2 rounded-full bg-blue-300 animate-pulse"></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li>
                <a href="/Uni-PHP/Assignment/products/read.php" 
                   class="flex items-center p-3 rounded-lg group transition-all duration-200
                          <?= strpos($_SERVER['PHP_SELF'], 'products/') !== false ? 
                             'bg-blue-600 shadow-lg' : 'hover:bg-blue-700/50' ?>">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center mr-3
                               <?= strpos($_SERVER['PHP_SELF'], 'products/') !== false ? 
                                  'bg-blue-500' : 'group-hover:bg-blue-500' ?>">
                        <i class="fas fa-boxes text-sm"></i>
                    </div>
                    <span class="font-medium">Products</span>
                    <?php if(strpos($_SERVER['PHP_SELF'], 'products/') !== false): ?>
                        <span class="ml-auto w-2 h-2 rounded-full bg-blue-300 animate-pulse"></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Footer Section -->
    <div class="px-4 py-3 border-t border-blue-700/50">
        <div class="flex items-center justify-between text-sm text-blue-200">
            <div class="flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                <span>v1.0.0</span>
            </div>
            <button class="p-1 rounded-full hover:bg-blue-700/50 transition-colors duration-200">
                <i class="fas fa-cog"></i>
            </button>
        </div>
    </div>
</div>

<!-- Mobile overlay (hidden by default) -->
<div class="fixed inset-0 bg-black/50 z-30 md:hidden hidden" id="sidebar-overlay"></div>


<!-- Combined JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // User dropdown functionality
    const userMenuButton = document.getElementById('user-menu-button');
    const userDropdown = document.getElementById('user-dropdown');
    let userDropdownOpen = false;

    userMenuButton.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdownOpen = !userDropdownOpen;
        
        if (userDropdownOpen) {
            userDropdown.classList.remove('hidden', 'opacity-0', 'scale-95');
            userDropdown.classList.add('opacity-100', 'scale-100');
        } else {
            userDropdown.classList.remove('opacity-100', 'scale-100');
            userDropdown.classList.add('opacity-0', 'scale-95');
            setTimeout(() => userDropdown.classList.add('hidden'), 150);
        }
    });

    // Close user dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (userDropdownOpen && !userMenuButton.contains(e.target) && !userDropdown.contains(e.target)) {
            userDropdown.classList.remove('opacity-100', 'scale-100');
            userDropdown.classList.add('opacity-0', 'scale-95');
            setTimeout(() => userDropdown.classList.add('hidden'), 150);
            userDropdownOpen = false;
        }
    });

    // Sidebar functionality
    const sidebar = document.querySelector('.w-64');
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const sidebarCloseButton = document.getElementById('sidebar-close-button');
    const overlay = document.getElementById('sidebar-overlay');
    let sidebarOpen = false;

    // Toggle sidebar
    function toggleSidebar() {
        sidebarOpen = !sidebarOpen;
        if (sidebarOpen) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    mobileMenuButton.addEventListener('click', toggleSidebar);
    sidebarCloseButton.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);

    // Responsive behavior
    function handleResize() {
        if (window.innerWidth >= 768) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            sidebarOpen = false;
        } else if (sidebarOpen) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }

    // Initial check
    handleResize();
    window.addEventListener('resize', handleResize);
});
</script>