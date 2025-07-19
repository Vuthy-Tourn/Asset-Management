<?php
// header.php
// Get current page title based on the current script
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

$page_title = 'Dashboard';
if (strpos($_SERVER['PHP_SELF'], 'floor/') !== false) $page_title = 'Floors';
if (strpos($_SERVER['PHP_SELF'], 'category/') !== false) $page_title = 'Categories';
if (strpos($_SERVER['PHP_SELF'], 'products/') !== false) $page_title = 'Products';
if (strpos($_SERVER['PHP_SELF'], 'analytics/') !== false) $page_title = 'Analytics';
if (strpos($_SERVER['PHP_SELF'], 'settings/') !== false) $page_title = 'Settings';
if (strpos($_SERVER['PHP_SELF'], 'recent-activites/') !== false) $page_title = 'Recent Activities';

// Database connection (replace with your actual connection)


// Function to generate notifications based on recent changes
function generateRecentNotifications($conn)
{
    $notifications = [];
    $now = new DateTime();

    // Get recent floor changes (last 24 hours)
    $floorQuery = "SELECT * FROM floor WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER BY created_at DESC LIMIT 3";
    $floorResult = mysqli_query($conn, $floorQuery);

    while ($floor = mysqli_fetch_assoc($floorResult)) {
        $notifications[] = [
            'id' => 'floor_' . $floor['id'],
            'title' => 'New Floor Added',
            'message' => 'Floor "' . htmlspecialchars($floor['name']) . '" has been added',
            'type' => 'success',
            'time' => timeAgo($floor['created_at']),
            'read' => false,
            'icon' => 'fas fa-building'
        ];
    }

    // Get recent category changes (last 24 hours)
    $categoryQuery = "SELECT c.*, f.name as floor_name FROM category c 
                     JOIN floor f ON c.floor_id = f.id 
                     WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) 
                     ORDER BY c.created_at DESC LIMIT 3";
    $categoryResult = mysqli_query($conn, $categoryQuery);

    while ($category = mysqli_fetch_assoc($categoryResult)) {
        $notifications[] = [
            'id' => 'category_' . $category['id'],
            'title' => 'New Category Added',
            'message' => 'Category "' . htmlspecialchars($category['name']) . '" added to floor ' . htmlspecialchars($category['floor_name']),
            'type' => 'info',
            'time' => timeAgo($category['created_at']),
            'read' => false,
            'icon' => 'fas fa-tag'
        ];
    }

    // Get recent product changes (last 24 hours)
    $productQuery = "SELECT p.*, c.name as category_name FROM products p 
                    JOIN category c ON p.category_id = c.id 
                    WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) 
                    ORDER BY p.created_at DESC LIMIT 3";
    $productResult = mysqli_query($conn, $productQuery);

    while ($product = mysqli_fetch_assoc($productResult)) {
        $notifications[] = [
            'id' => 'product_' . $product['id'],
            'title' => 'New Product Added',
            'message' => 'Product "' . htmlspecialchars($product['name']) . '" added to category ' . htmlspecialchars($product['category_name']),
            'type' => 'success',
            'time' => timeAgo($product['created_at']),
            'read' => false,
            'icon' => 'fas fa-box'
        ];
    }

    // Sort all notifications by time (newest first)
    usort($notifications, function ($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });

    return $notifications;
}

// Helper function to convert datetime to "time ago" format
function timeAgo($datetime)
{
    $time = strtotime($datetime);
    if ($time === false) {
        return "Invalid date"; // Handle invalid dates gracefully
    }

    $now = time();
    $diff = $now - $time;

    // Handle future dates (edge case)
    if ($diff < 0) {
        return "Just now";
    }

    // Seconds
    if ($diff < 60) {
        return $diff == 1 ? "1 second ago" : "$diff seconds ago";
    }

    // Minutes
    $minutes = floor($diff / 60);
    if ($minutes < 60) {
        return $minutes == 1 ? "1 minute ago" : "$minutes minutes ago";
    }

    // Hours
    $hours = floor($diff / 3600);
    if ($hours < 24) {
        return $hours == 1 ? "1 hour ago" : "$hours hours ago";
    }

    // Days
    $days = floor($diff / 86400);
    if ($days < 7) {
        return $days == 1 ? "1 day ago" : "$days days ago";
    }

    // Fallback to formatted date
    return date('M j, Y', $time);
}

// Get notifications by checking recent changes
$notifications = generateRecentNotifications($conn);

// Count unread notifications (all are unread in this implementation)
$unread_count = count($notifications);

// Check for dark mode preference
$dark_mode = isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true';
?>


<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    * {
        font-family: 'Inter', sans-serif;
    }

    .glass-effect {
        backdrop-filter: blur(16px);
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .notification-pulse {
        animation: pulse-scale 2s infinite;
    }

    @keyframes pulse-scale {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }

    .smooth-transition {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .search-glow:focus {
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .notification-item:hover {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(168, 85, 247, 0.05));
    }

    .notification-unread {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(147, 51, 234, 0.05));
        border-left: 3px solid #3b82f6;
    }

    .mobile-menu-btn span {
        transition: all 0.3s ease;
        transform-origin: center;
    }

    .mobile-menu-active span:nth-child(1) {
        transform: rotate(45deg) translate(5px, 5px);
    }

    .mobile-menu-active span:nth-child(2) {
        opacity: 0;
    }

    .mobile-menu-active span:nth-child(3) {
        transform: rotate(-45deg) translate(7px, -6px);
    }
</style>

<header class="glass-effect sticky top-0 z-40 border-b border-gray-200/50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Mobile menu button -->
            <div class="flex items-center md:hidden">
                <button type="button" id="mobile-menu-button"
                    class="mobile-menu-btn inline-flex items-center justify-center p-2 rounded-xl text-gray-600 hover:text-gray-900 hover:bg-gray-100/80 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 smooth-transition">
                    <span class="sr-only">Open main menu</span>
                    <div class="relative w-6 h-6">
                        <span class="absolute top-1 left-0 w-full h-0.5 bg-current block smooth-transition"></span>
                        <span class="absolute top-2.5 left-0 w-full h-0.5 bg-current block smooth-transition"></span>
                        <span class="absolute top-4 left-0 w-full h-0.5 bg-current block smooth-transition"></span>
                    </div>
                </button>
            </div>

            <!-- Title/Logo -->
            <div class="flex items-center flex-1 lg:flex-none">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-bold text-gray-900 hidden sm:block">
                        <span id="page-title"><?php echo $page_title; ?></span>
                    </h1>
                </div>
            </div>

            <!-- Right side items -->
            <div class="flex items-center space-x-3">

                <!-- Notifications -->
                <div class="relative">
                    <button id="notifications-btn" class="p-2 rounded-xl text-gray-600 hover:text-gray-900 hover:bg-gray-100/80 focus:outline-none focus:ring-2 focus:ring-indigo-500 smooth-transition relative group">
                        <i class="fas fa-bell text-lg"></i>
                        <?php if ($unread_count > 0): ?>
                            <span class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-gradient-to-r from-red-500 to-pink-500 flex items-center justify-center text-white text-xs font-medium notification-pulse">
                                <?php echo $unread_count; ?>
                            </span>
                        <?php endif; ?>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div id="notifications-dropdown"
                        class="hidden absolute right-0 mt-2 w-80 rounded-2xl shadow-2xl glass-effect ring-1 ring-black ring-opacity-5 focus:outline-none smooth-transition transform opacity-0 scale-95 origin-top-right max-h-96 overflow-hidden">

                        <!-- Header -->
                        <div class="px-4 py-3 border-b border-gray-200/50">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-gray-500"><?php echo $unread_count; ?> unread</span>
                                    <button id="mark-all-read" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                        Mark all read
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications List -->
                        <div class="max-h-64 overflow-y-auto">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item p-4 border-b border-gray-100/50 smooth-transition cursor-pointer <?php echo !$notification['read'] ? 'notification-unread' : ''; ?>"
                                    data-notification-id="<?php echo $notification['id']; ?>">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                                <i class="<?php echo $notification['icon']; ?> text-white text-sm"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-semibold text-gray-900 truncate">
                                                    <?php echo htmlspecialchars($notification['title']); ?>
                                                </p>
                                                <?php if (!$notification['read']): ?>
                                                    <div class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></div>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                                <?php echo htmlspecialchars($notification['message']); ?>
                                            </p>
                                            <p class="text-xs text-gray-400 mt-1">
                                                <?php echo $notification['time']; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Footer -->
                        <div class="px-4 py-3 border-t border-gray-200/50 bg-gray-50/50">
                            <a href="/Uni-PHP/Assignment/pages/recent_data.php" class="block text-center text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                View all notifications
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="relative">
                    <button id="quick-actions-btn" class="p-2 rounded-xl text-gray-600 hover:text-gray-900 hover:bg-gray-100/80 smooth-transition">
                        <i class="fas fa-plus text-lg"></i>
                    </button>

                    <!-- Quick Actions Dropdown -->
                    <div id="quick-actions-dropdown"
                        class="hidden absolute right-0 mt-2 w-56 rounded-2xl shadow-2xl glass-effect ring-1 ring-black ring-opacity-5 focus:outline-none smooth-transition transform opacity-0 scale-95 origin-top-right">
                        <div class="py-2">
                            <a href="/Uni-PHP/Assignment/products/read.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50/80 smooth-transition">
                                <i class="fas fa-plus-circle w-4 mr-3 text-green-500"></i>
                                Add New Product
                            </a>
                            <a href="/Uni-PHP/Assignment/category/read.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50/80 smooth-transition">
                                <i class="fas fa-tag w-4 mr-3 text-blue-500"></i>
                                Add Category
                            </a>
                            <a href="/Uni-PHP/Assignment/floor/read.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50/80 smooth-transition">
                                <i class="fas fa-building w-4 mr-3 text-purple-500"></i>
                                Add Floor
                            </a>
                        </div>
                    </div>
                </div>

                <!-- User Profile -->
                <div class="relative">
                    <button type="button" id="user-menu-button"
                        class="flex items-center space-x-3 p-2 rounded-xl hover:bg-gray-100/80 focus:outline-none smooth-transition group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#0345e4] via-[#026af2] to-[#00279c] flex items-center justify-center text-white shadow-lg hover-lift smooth-transition">
                            <i class="fas fa-user text-sm"></i>
                        </div>
                        <span class="text-gray-700 hidden sm:inline text-sm font-medium">Admin</span>
                    </button>

                    <!-- User Dropdown menu -->
                    <div id="user-dropdown"
                        class="hidden absolute right-0 mt-2 w-64 rounded-2xl shadow-2xl glass-effect ring-1 ring-black ring-opacity-5 divide-y divide-gray-200/50 focus:outline-none smooth-transition transform opacity-0 scale-95 origin-top-right">
                        <div class="px-4 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#0345e4] via-[#026af2] to-[#00279c] flex items-center justify-center text-white">
                                    <!-- Display user's initials if available -->
                                    <?php if (!empty($_SESSION['name'])): ?>
                                        <?= substr($_SESSION['name'], 0, 1) ?>
                                    <?php else: ?>
                                        <i class="fas fa-user"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <!-- Display user's name -->
                                    <p class="text-sm font-semibold text-gray-900">
                                        <?= !empty($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Admin' ?>
                                    </p>

                                    <!-- Display user's email -->
                                    <p class="text-xs text-gray-500">
                                        <?= !empty($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'admin@example.com' ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="py-2">
                            <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50/80 smooth-transition">
                                <i class="fas fa-user-circle w-4 mr-3"></i>
                                Profile
                            </a>
                            <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50/80 smooth-transition">
                                <i class="fas fa-cog w-4 mr-3"></i>
                                Settings
                            </a>
                            <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50/80 smooth-transition">
                                <i class="fas fa-question-circle w-4 mr-3"></i>
                                Help
                            </a>
                        </div>
                        <div class="py-2">
                            <a href="../auth/logout.php" class="flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50/80 smooth-transition">
                                <i class="fas fa-sign-out-alt w-4 mr-3"></i>
                                Sign out
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile menu button animation
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        let mobileMenuOpen = false;

        mobileMenuButton.addEventListener('click', function() {
            mobileMenuOpen = !mobileMenuOpen;
            this.classList.toggle('mobile-menu-active', mobileMenuOpen);
        });


        // Dark mode toggle
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', function() {
                const isDark = document.documentElement.classList.toggle('dark');
                document.cookie = `dark_mode=${isDark}; path=/; max-age=31536000`; // 1 year
                this.innerHTML = isDark ?
                    '<i class="fas fa-sun"></i>' :
                    '<i class="fas fa-moon"></i>';
            });
        }

        // Dropdown functionality (same as before)
        function setupDropdown(buttonId, dropdownId) {
            const button = document.getElementById(buttonId);
            const dropdown = document.getElementById(dropdownId);
            let isOpen = false;

            if (!button || !dropdown) return;

            button.addEventListener('click', function(e) {
                e.stopPropagation();
                isOpen = !isOpen;

                if (isOpen) {
                    dropdown.classList.remove('hidden', 'opacity-0', 'scale-95');
                    dropdown.classList.add('opacity-100', 'scale-100');
                } else {
                    dropdown.classList.remove('opacity-100', 'scale-100');
                    dropdown.classList.add('opacity-0', 'scale-95');
                    setTimeout(() => dropdown.classList.add('hidden'), 150);
                }
            });

            document.addEventListener('click', function(e) {
                if (isOpen && !button.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('opacity-100', 'scale-100');
                    dropdown.classList.add('opacity-0', 'scale-95');
                    setTimeout(() => dropdown.classList.add('hidden'), 150);
                    isOpen = false;
                }
            });

            return {
                button,
                dropdown,
                isOpen: () => isOpen
            };
        }

        // Setup all dropdowns
        const userDropdown = setupDropdown('user-menu-button', 'user-dropdown');
        const notificationsDropdown = setupDropdown('notifications-btn', 'notifications-dropdown');
        const quickActionsDropdown = setupDropdown('quick-actions-btn', 'quick-actions-dropdown');

        // Notification functionality
        const notificationItems = document.querySelectorAll('.notification-item');
        const markAllReadBtn = document.getElementById('mark-all-read');

        // Mark individual notification as read
        notificationItems.forEach(item => {
            item.addEventListener('click', function() {
                if (this.classList.contains('notification-unread')) {
                    const notificationId = this.getAttribute('data-notification-id');
                    this.classList.remove('notification-unread');
                    const unreadDot = this.querySelector('.w-2.h-2.bg-blue-500');
                    if (unreadDot) unreadDot.remove();
                    updateNotificationBadge();

                    // Store in localStorage that this notification was read
                    const readNotifications = JSON.parse(localStorage.getItem('readNotifications') || '[]');
                    readNotifications.push(notificationId);
                    localStorage.setItem('readNotifications', JSON.stringify(readNotifications));
                }
            });
        });

        // Mark all notifications as read
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.notification-unread').forEach(element => {
                    element.classList.remove('notification-unread');
                    const unreadDot = element.querySelector('.w-2.h-2.bg-blue-500');
                    if (unreadDot) unreadDot.remove();

                    // Store all notification IDs as read
                    const notificationId = element.getAttribute('data-notification-id');
                    const readNotifications = JSON.parse(localStorage.getItem('readNotifications') || '[]');
                    if (!readNotifications.includes(notificationId)) {
                        readNotifications.push(notificationId);
                    }
                    localStorage.setItem('readNotifications', JSON.stringify(readNotifications));
                });
                updateNotificationBadge();
            });
        }

        function updateNotificationBadge() {
            const badge = document.querySelector('.notification-pulse');
            const unreadItems = document.querySelectorAll('.notification-unread').length;

            if (unreadItems === 0 && badge) {
                badge.remove();
            } else if (badge) {
                badge.textContent = unreadItems;
            }

            // Update the unread count in dropdown header
            const unreadCountSpan = document.querySelector('#notifications-dropdown .text-xs.text-gray-500');
            if (unreadCountSpan) {
                unreadCountSpan.textContent = `${unreadItems} unread`;
            }
        }

        // Check localStorage for read notifications and mark them
        function markReadNotifications() {
            const readNotifications = JSON.parse(localStorage.getItem('readNotifications') || '[]');

            document.querySelectorAll('.notification-item').forEach(item => {
                const notificationId = item.getAttribute('data-notification-id');
                if (readNotifications.includes(notificationId)) {
                    item.classList.remove('notification-unread');
                    const unreadDot = item.querySelector('.w-2.h-2.bg-blue-500');
                    if (unreadDot) unreadDot.remove();
                }
            });

            updateNotificationBadge();
        }

        // Initial marking of read notifications
        markReadNotifications();

        // Auto-refresh notifications every 5 minutes
        setInterval(function() {
            fetch('/Uni-PHP/Assignment/header.php?refresh=1')
                .then(response => response.text())
                .then(html => {
                    // This would require more complex DOM manipulation
                    // In a real app, you'd want to use an API endpoint that returns JSON
                    console.log('Refreshed notifications');
                })
                .catch(error => console.error('Error refreshing notifications:', error));
        }, 300000); // 5 minutes
    });
</script>