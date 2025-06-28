<?php

/**
 * Enhanced Page Header Component with Breadcrumbs and Action Buttons
 * 
 * @param string $title        Main header title
 * @param string $subtitle     Subtitle or description
 * @param array $buttonConfig  Action button configuration
 * @param array $breadcrumbs   Breadcrumb items array (optional)
 * @param string $additionalClasses Additional CSS classes
 */
function renderPageHeader(
    string $title,
    string $subtitle = '',
    array $buttonConfig = [],
    array $breadcrumbs = [],
    string $additionalClasses = ''
) {
    // Default button configuration
    $defaultButton = [
        'text' => 'Add New',
        'icon' => 'fa-plus',
        'modalId' => 'createModal',
        'modalUrl' => 'create.php',
        'modalTarget' => 'createContent',
        'variant' => 'primary',
        'size' => 'md' // sm, md, lg
    ];

    $button = array_merge($defaultButton, $buttonConfig);

    // Button variants with modern gradients
    $buttonVariants = [
        'primary' => 'bg-gradient-to-br from-[#0345e4] via-[#026af2] to-[#00279c]',
        'secondary' => 'bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600',
        'danger' => 'bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700',
        'success' => 'bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700',
        'light' => 'bg-gray-100 hover:bg-gray-200 text-gray-800 border border-gray-300'
    ];

    // Button sizes
    $buttonSizes = [
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-5 py-2.5 text-base',
        'lg' => 'px-6 py-3 text-lg'
    ];
?>
    <div class="mb-8 <?= htmlspecialchars($additionalClasses) ?> px-2">
        <?php if (!empty($breadcrumbs)): ?>
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                    <?php foreach ($breadcrumbs as $index => $crumb): ?>
                        <li class="inline-flex items-center">
                            <?php if ($index > 0): ?>
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            <?php endif; ?>
                            <?php if (isset($crumb['url'])): ?>
                                <a href="<?= htmlspecialchars($crumb['url']) ?>" class="text-sm font-medium text-gray-700 hover:text-purple-600 inline-flex items-center">
                                    <?php if ($index === 0): ?>
                                        <i class="fas fa-home mr-2"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($crumb['title']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-sm font-medium text-gray-500">
                                    <?= htmlspecialchars($crumb['title']) ?>
                                </span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight">
                    <?= htmlspecialchars($title) ?>
                </h1>
                <?php if ($subtitle): ?>
                    <p class="mt-2 text-gray-600 max-w-3xl">
                        <?= htmlspecialchars($subtitle) ?>
                    </p>
                <?php endif; ?>
            </div>

            <?php if (!empty($button['text'])): ?>
                <div class="flex-shrink-0">
                    <button
                        id="<?= htmlspecialchars($button['modalId']) ?>Btn"
                        data-modal-fetch="<?= htmlspecialchars($button['modalId']) ?>"
                        data-modal-url="<?= htmlspecialchars($button['modalUrl']) ?>"
                        data-modal-target="<?= htmlspecialchars($button['modalTarget']) ?>"
                        class="<?= $buttonVariants[$button['variant']] ?? $buttonVariants['primary'] ?> <?= $buttonSizes[$button['size']] ?? $buttonSizes['md'] ?> text-white font-medium rounded-lg flex items-center transition-all duration-200 shadow hover:shadow-lg transform hover:-translate-y-0.5 focus:ring-1 focus:ring-offset-2 focus:ring-[#0345e4]-500 focus:outline-none">
                        <i class="fas <?= htmlspecialchars($button['icon']) ?> mr-2"></i>
                        <?= htmlspecialchars($button['text']) ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
}
