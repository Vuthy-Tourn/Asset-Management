<?php

/**
 * Reusable Page Header Component
 * 
 * @param string $title
 * @param string $subtitle
 * @param array $buttonConfig
 * @param string $additionalClasses
 */
function renderPageHeader(
    string $title,
    string $subtitle = '',
    array $buttonConfig = [],
    string $additionalClasses = ''
) {
    $defaultButton = [
        'text' => 'Add New',
        'icon' => 'fa-plus',
        'modalId' => 'createModal',
        'modalUrl' => 'create.php',
        'modalTarget' => 'createContent',
        'variant' => 'primary'
    ];

    $button = array_merge($defaultButton, $buttonConfig);

    $buttonVariants = [
        'primary' => 'bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800',
        'secondary' => 'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800',
        'danger' => 'bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800'
    ];
?>
    <div class="flex flex-col lg:flex-row justify-between items-center mb-6 gap-4 <?= htmlspecialchars($additionalClasses) ?>">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($title) ?></h1>
            <?php if ($subtitle): ?>
                <p class="text-gray-600 mt-1"><?= htmlspecialchars($subtitle) ?></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($button['text'])): ?>
            <button
                id="<?= htmlspecialchars($button['modalId']) ?>Btn"
                data-modal-fetch="<?= htmlspecialchars($button['modalId']) ?>"
                data-modal-url="<?= htmlspecialchars($button['modalUrl']) ?>"
                data-modal-target="<?= htmlspecialchars($button['modalTarget']) ?>"
                class="<?= $buttonVariants[$button['variant']] ?? $buttonVariants['primary'] ?> text-white px-6 py-3 rounded-lg flex items-center transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                <i class="fas <?= htmlspecialchars($button['icon']) ?> mr-2"></i>
                <?= htmlspecialchars($button['text']) ?>
            </button>
        <?php endif; ?>
    </div>
<?php
}
