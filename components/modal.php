<?php
function renderModal(
    string $modalId,
    string $title,
    string $icon,
    string $contentId,
    string $size = 'large', // small, medium, large, xlarge
    bool $includeDefaultContentDiv = true,
    string $additionalClasses = '',
) {
    $sizes = [
        'small' => 'max-w-md',
        'medium' => 'max-w-2xl',
        'large' => 'max-w-4xl',
        'xlarge' => 'max-w-6xl'
    ];

    $sizeClass = $sizes[$size] ?? $sizes['large'];
?>
    <div id="<?= htmlspecialchars($modalId) ?>"
        class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4"
        aria-hidden="true"
        aria-labelledby="<?= htmlspecialchars($modalId) ?>-title"
        role="dialog">
        <div class="modal-content bg-white rounded-lg shadow-xl transform transition-all p-4 <?= $sizeClass ?> w-full <?= htmlspecialchars($additionalClasses) ?>"
            role="document">
            <!-- Header -->
            <div class="flex justify-between items-center border-b border-gray-200 p-4">
                <h3 id="<?= htmlspecialchars($modalId) ?>-title" class="text-2xl font-semibold text-gray-800 flex items-center">
                    <i class=" <?= htmlspecialchars($icon) ?> text-green-600 mr-3"></i>
                    <?= htmlspecialchars($title) ?>
                </h3>

                <button type="button"
                    data-modal-close="<?= htmlspecialchars($modalId) ?>"
                    class="text-gray-500 hover:text-gray-700 text-2xl focus:outline-none"
                    aria-label="Close modal">
                    &times;
                </button>
            </div>

            <?php if ($includeDefaultContentDiv): ?>
                <div id="<?= htmlspecialchars($contentId) ?>" class="p-4">
                    <div class="flex justify-center items-center py-8 loading-content">
                        <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-purple-600"></div>
                    </div>
                    <div class="actual-content hidden"></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Ensure modal target exists in DOM
        document.addEventListener('DOMContentLoaded', function() {
            if (!document.getElementById('<?= htmlspecialchars($contentId) ?>')) {
                console.error('Modal target element #<?= htmlspecialchars($contentId) ?> not found');
            }
        });
    </script>
<?php
}

/**
 * Enhanced Delete Confirmation Modal
 */
function renderDeleteModal(
    string $modalId = 'deleteConfirmModal',
    string $title = 'Confirm Deletion',
    string $message = 'Are you sure you want to delete this item? This action cannot be undone.',
    string $deleteButtonText = 'Delete',
    string $cancelButtonText = 'Cancel'
) {
?>
    <div id="<?= htmlspecialchars($modalId) ?>"
        class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4"
        aria-hidden="true"
        aria-labelledby="<?= htmlspecialchars($modalId) ?>-title"
        role="dialog">
        <div class="modal-content bg-white rounded-lg shadow-xl transform transition-all max-w-md w-full"
            role="document">
            <div class="flex justify-between items-center border-b border-gray-200 p-4">
                <h3 id="<?= htmlspecialchars($modalId) ?>-title" class="text-xl font-bold text-gray-900">
                    <?= htmlspecialchars($title) ?>
                </h3>
                <button type="button"
                    data-modal-close="<?= htmlspecialchars($modalId) ?>"
                    class="text-gray-500 hover:text-gray-700 text-2xl focus:outline-none"
                    aria-label="Close modal">
                    &times;
                </button>
            </div>

            <div class="p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl mr-3"></i>
                    <p class="text-gray-700"><?= htmlspecialchars($message) ?></p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button"
                        data-modal-close="<?= htmlspecialchars($modalId) ?>"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        <?= htmlspecialchars($cancelButtonText) ?>
                    </button>
                    <button type="button"
                        data-modal-confirm-delete="<?= htmlspecialchars($modalId) ?>"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        <i class="fas fa-trash mr-2"></i>
                        <?= htmlspecialchars($deleteButtonText) ?>
                    </button>
                </div>
            </div>
        </div>
    </div>


<?php
}

/**
 * Preconfigured Loading Modal
 */
function renderLoadingModal(string $modalId = 'loadingModal')
{
?>
    <div id="<?= htmlspecialchars($modalId) ?>" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-sm w-full text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-purple-600 mx-auto mb-4"></div>
            <p class="text-gray-700">Loading, please wait...</p>
        </div>
    </div>
<?php
}
