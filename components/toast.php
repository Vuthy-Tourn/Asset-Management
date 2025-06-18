<?php
// toast.php
function showToast(string $message, string $type = 'success')
{
    $icon = $type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    $bgColor = $type === 'success' ? 'bg-green-500' : 'bg-red-500';
?>
    <div id="toast" class="toast fixed top-4 right-4 <?= $bgColor ?> text-white px-6 py-4 rounded-lg shadow-lg flex items-center max-w-sm">
        <i class="fas <?= $icon ?> mr-3 text-xl"></i>
        <span><?= htmlspecialchars($message) ?></span>
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
<?php
}
