<?php
// toast.php
require_once __DIR__ . '/flash.php';

function showToast(string $message = '', string $type = 'success')
{
    // Check for flash messages first
    $flash = flash();
    if ($flash) {
        $message = $flash['message'];
        $type = $flash['type'];
    }

    if ($message) {
        $icon = $type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        $bgColor = $type === 'success' ? 'bg-green-500' : 'bg-red-500';
?>
        <div id="toast" class="toast z-50 fixed top-4 right-4 <?= $bgColor ?> text-white px-6 py-4 rounded-lg shadow-lg flex items-center max-w-sm">
            <i class="fas <?= $icon ?> mr-3 text-xl"></i>
            <span><?= htmlspecialchars($message) ?></span>
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
<?php
    }
}
