<?php
require_once '../components/config/db.php';

$recentActivity = $conn->query("
    (SELECT 'floor' as type, name, created_at, 'added' as action, 'building' as icon FROM floor ORDER BY created_at DESC LIMIT 5)
    UNION ALL
    (SELECT 'category' as type, name, created_at, 'added' as action, 'tags' as icon FROM category ORDER BY created_at DESC LIMIT 5)
    UNION ALL
    (SELECT 'product' as type, name, created_at, 'added' as action, 'box' as icon FROM products ORDER BY created_at DESC LIMIT 5)
    ORDER BY created_at DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Recently Data</title>
    <style></style>
</head>

<body>
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include '../components/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Header -->
            <?php include '../components/header.php'; ?>
            <!-- Dynamic Recent Activity with Pagination -->
            <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-xl p-8 lg:p-10 border border-white/30 relative overflow-hidden">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold text-slate-800 mb-2">Recent Activity</h2>
                        <p class="text-slate-600 text-sm">Latest updates from your system</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition-colors duration-200" onclick="refreshActivity()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>

                <!-- Activity Container -->
                <div id="activity-container">
                    <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 gap-4" id="activity-list">
                        <!-- PHP Activity Items Will Be Rendered Here -->
                        <?php
                        // Calculate pagination
                        $itemsPerPage = 6;
                        $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                        $totalItems = count($recentActivity ?? []);
                        $totalPages = ceil($totalItems / $itemsPerPage);
                        $startIndex = ($currentPage - 1) * $itemsPerPage;
                        $currentPageItems = array_slice($recentActivity ?? [], $startIndex, $itemsPerPage);
                        ?>

                        <?php if (empty($recentActivity)): ?>
                            <div class="text-center py-12">
                                <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-inbox text-3xl text-slate-400"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-slate-600 mb-2">No Recent Activity</h3>
                                <p class="text-slate-500">Start adding floors, categories, or products to see activity here.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($currentPageItems as $index => $activity): ?>
                                <?php $style = getActivityStyle($activity['type']); ?>
                                <div class="group flex items-start p-4 rounded-2xl bg-gradient-to-r <?= $style['bg'] ?> border <?= $style['border'] ?> hover:shadow-lg transition-all duration-300 animate-fade-in" style="animation-delay: <?= $index * 0.1 ?>s;">
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

                    <!-- Pagination Component -->
                    <?php if (!empty($recentActivity) && $totalPages > 1): ?>
                        <div class="mt-8">
                            <div class="pagination-container"
                                data-current-page="<?= $currentPage ?>"
                                data-total-pages="<?= $totalPages ?>"
                                data-total-items="<?= $totalItems ?>">
                                <!-- Pagination will be rendered by JavaScript -->
                            </div>
                        </div>
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
        </div>
    </div>

    <script src="../js/pagination.js"></script>
</body>

</html>