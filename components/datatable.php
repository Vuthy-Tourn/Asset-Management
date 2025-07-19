<?php
require_once __DIR__ . '/config/db.php';
require_once 'flash.php';

class DataTable
{
    private $conn;
    private $config = [];
    private $data = [];
    private $total = 0;
    private $totalPages = 0;
    private $currentPage = 1;
    private $filters = [];
    private $searchTerm = '';
    private $timeFilter = '';

    public function __construct($dbConnection, $config = [])
    {
        $this->conn = $dbConnection;
        $this->config = array_merge([
            'table' => '',
            'primaryKey' => 'id',
            'columns' => [],
            'joins' => [],
            'searchable' => [],
            'filterOptions' => [], // Define filter options for dropdowns
            // Add time filter to config defaults
            'timeFilterOptions' => [
                '' => 'All Time',
                'today' => 'Today',
                'week' => 'This Week',
                'month' => 'This Month',
                'year' => 'This Year'
            ],
            'dateField' => 'created_at', // Default date field to filter on
            'defaultOrder' => 'created_at ASC',
            'perPage' => 10,
            'actions' => true,
            'emptyMessage' => 'No records found',
            'emptySearchMessage' => 'No results found',
            'addButton' => false,
            'addButtonText' => 'Add New',
            'addButtonModal' => '',
        ], $config);

        $this->processFilters();
    }

    private function processFilters()
    {
        foreach ($this->config['filterOptions'] as $filterName => $options) {
            if (isset($_GET[$filterName]) && $_GET[$filterName] !== '') {
                $this->filters[$filterName] = $_GET[$filterName];
            }
        }
        // Capture search term
        if (isset($_GET['search']) && trim($_GET['search']) !== '') {
            $this->searchTerm = trim($_GET['search']);
        }

        // Time filter processing - ensure it's always set if in GET params
        $this->timeFilter = $_GET['time_filter'] ?? '';
    }

    private function fetchData()
    {
        // Get current page from URL first
        $this->currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($this->currentPage - 1) * $this->config['perPage'];
        $whereClauses = [];
        $params = [];
        $types = '';

        // Build filter conditions
        foreach ($this->filters as $column => $value) {
            if ($value !== '') { // Only add if value is not empty
                $whereClauses[] = "$column = ?";
                $params[] = $value;
                $types .= 's';
            }
        }

        // Build search conditions
        if (!empty($this->searchTerm) && !empty($this->config['searchable'])) {
            $searchParts = [];
            foreach ($this->config['searchable'] as $column) {
                $searchParts[] = "$column LIKE ?";
                $params[] = '%' . $this->searchTerm . '%';
                $types .= 's';
            }
            $whereClauses[] = '(' . implode(' OR ', $searchParts) . ')';
        }

        // Add time filter conditions
        if (!empty($this->timeFilter)) {
            $dateField = $this->config['dateField'] ?? 'created_at';

            switch ($this->timeFilter) {
                case 'today':
                    $whereClauses[] = "DATE($dateField) = CURDATE()";
                    break;
                case 'week':
                    $whereClauses[] = "YEARWEEK($dateField, 1) = YEARWEEK(CURDATE(), 1)";
                    break;
                case 'month':
                    $whereClauses[] = "MONTH($dateField) = MONTH(CURDATE()) AND YEAR($dateField) = YEAR(CURDATE())";
                    break;
                case 'year':
                    $whereClauses[] = "YEAR($dateField) = YEAR(CURDATE())";
                    break;
                case 'latest':
                    $this->config['defaultOrder'] = "$dateField DESC";
                    break;
            }
        }

        $whereCondition = empty($whereClauses) ? '' : 'WHERE ' . implode(' AND ', $whereClauses);

        try {
            // Get total count
            $countSql = "SELECT COUNT(*) FROM {$this->config['table']}";
            if (!empty($this->config['joins'])) {
                foreach ($this->config['joins'] as $join) {
                    $countSql .= " $join";
                }
            }
            $countSql .= " $whereCondition";

            $countStmt = $this->conn->prepare($countSql);
            if (!empty($params)) {
                $countStmt->bind_param($types, ...$params);
            }
            $countStmt->execute();
            $this->total = $countStmt->get_result()->fetch_row()[0];
            $this->totalPages = ceil($this->total / $this->config['perPage']);

            // Get data
            $sql = "SELECT {$this->config['table']}.*";
            if (!empty($this->config['joins'])) {
                foreach ($this->config['joins'] as $join) {
                    if (preg_match('/LEFT JOIN (\w+)/', $join, $matches)) {
                        $joinedTable = $matches[1];
                        $sql .= ", $joinedTable.name as {$joinedTable}_name";
                    }
                }
            }
            $sql .= " FROM {$this->config['table']}";

            if (!empty($this->config['joins'])) {
                foreach ($this->config['joins'] as $join) {
                    $sql .= " $join";
                }
            }

            $sql .= " $whereCondition";
            $sql .= " ORDER BY {$this->config['defaultOrder']}";
            $sql .= " LIMIT ? OFFSET ?";

            $stmt = $this->conn->prepare($sql);
            $params[] = $this->config['perPage'];
            $params[] = $offset;
            $types .= 'ii';

            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $this->data = [];
            while ($row = $result->fetch_assoc()) {
                $this->data[] = $row;
            }
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
            $this->data = [];
            $this->total = 0;
            $this->totalPages = 0;
        }
    }

    private function getFilterOptions($filterName)
    {
        // You can customize this method to fetch options from database if needed
        return $this->config['filterOptions'][$filterName];
    }

    private function renderSearchAndFilters()
    {
        $hasFilters = !empty($this->config['filterOptions']) || !empty($this->config['timeFilterOptions']);
?>
        <div class="rounded-xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="p-5">
                <!-- Search and Filters Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                    <!-- Search Bar -->
                    <div class="flex-1 max-w-2xl">
                        <form id="searchForm" method="GET" action="" class="flex flex-col sm:flex-row gap-4 items-center"
                            data-live-search="true">
                            <div class="relative flex-1 max-w-md">
                                <input
                                    type="text"
                                    id="searchInput"
                                    name="search"
                                    value="<?= htmlspecialchars($this->searchTerm) ?>"
                                    placeholder="Search <?= $this->config['table'] ?>..."
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0345e4] focus:border-transparent transition-all duration-200"
                                    autocomplete="off">
                                <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
                                <!-- Add loading spinner if needed -->
                                <!-- <div id="searchLoading" class="hidden absolute right-3 top-4">
                    <i class="fas fa-spinner fa-spin text-gray-400"></i>
                </div> -->
                            </div>
                            <?php foreach ($this->filters as $name => $value): ?>
                                <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($value) ?>">
                            <?php endforeach; ?>
                        </form>
                    </div>

                    <!-- Filters Toggle -->
                    <?php if ($hasFilters): ?>
                        <div class="flex items-center space-x-3 hidden md:flex">
                            <?php if (!empty($this->filters) || !empty($this->searchTerm)): ?>
                                <a href="<?= strtok($_SERVER['REQUEST_URI'], '?') ?>" class="inline-flex items-center px-3.5 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0345e4]">
                                    Reset All
                                </a>
                            <?php endif; ?>
                            <button @click="filtersOpen = !filtersOpen" type="button" class="inline-flex items-center px-3.5 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0345e4]">
                                <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                                </svg>
                                Filters
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Filters Panel -->
                <?php if ($hasFilters): ?>
                    <div x-data="{ filtersOpen: false }" x-cloak>
                        <div x-show="filtersOpen" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="pt-4 border-t border-gray-200">
                            <form id="filterForm" method="get" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <?php if (!empty($this->config['filterOptions'])): ?>
                                        <?php foreach ($this->config['filterOptions'] as $filterName => $options): ?>
                                            <div>
                                                <label for="<?= $filterName ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                                    Filter by <?= ucfirst(str_replace('_id', ' ', $filterName)) ?>
                                                </label>
                                                <select
                                                    id="<?= $filterName ?>"
                                                    name="<?= $filterName ?>"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#0345e4] focus:border-[#0345e4]">
                                                    <?php foreach ($this->getFilterOptions($filterName) as $value => $label): ?>
                                                        <option value="<?= htmlspecialchars($value) ?>"
                                                            <?= isset($this->filters[$filterName]) && $this->filters[$filterName] == $value ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($label) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <?php if (!empty($this->config['timeFilterOptions'])): ?>
                                        <div>
                                            <label for="time_filter" class="block text-sm font-medium text-gray-700 mb-1">
                                                Time Period
                                            </label>
                                            <div class="mt-1 relative">
                                                <select id="time_filter" name="time_filter" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#0345e4] focus:border-[#0345e4] rounded-md shadow-sm">
                                                    <?php foreach ($this->config['timeFilterOptions'] as $value => $label): ?>
                                                        <option value="<?= htmlspecialchars($value) ?>" <?= $this->timeFilter === $value ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($label) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Hidden fields -->
                                <?php if (!empty($this->searchTerm)): ?>
                                    <input type="hidden" name="search" value="<?= htmlspecialchars($this->searchTerm) ?>">
                                <?php endif; ?>
                                <input type="hidden" name="page" value="1">
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php
    }

    public function render()
    {
        $this->fetchData();
        // Handle AJAX requests
        if (!empty($_GET['ajax']) || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            ob_start();
            $this->renderTable();
            $output = ob_get_clean();

            if (!empty($_GET['ajax'])) {
                echo $output;
                exit;
            }
            return $output;
        }

        $this->renderSearchAndFilters();
        $this->renderTable();
    }


    private function renderTable()
    {
    ?>
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <?php if (empty($this->data)): ?>
                
                <div class="text-center py-12">
                    <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">
                        <?= !empty($this->searchTerm) ? $this->config['emptySearchMessage'] : $this->config['emptyMessage'] ?>
                    </h3>
                    <p class="text-gray-500 mb-6">
                        <?= !empty($this->searchTerm) ? 'Try adjusting your search terms' : '' ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <?php foreach ($this->config['columns'] as $column): ?>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <?= $column['label'] ?>
                                    </th>
                                <?php endforeach; ?>
                                <?php if ($this->config['actions']): ?>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($this->data as $index => $row): ?>
                                <tr class="<?= $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' ?>">
                                    <?php foreach ($this->config['columns'] as $column): ?>
                                        <td class="px-6 py-4 <?= $column['nowrap'] ?? false ? 'whitespace-nowrap' : '' ?>">
                                            <?= $this->renderColumn($row, $column) ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <?php if ($this->config['actions']): ?>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-3">
                                                <button
                                                    class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                                    data-modal-fetch="edit<?= ucfirst($this->config['table']) ?>Modal"
                                                    data-modal-url="update.php?id=<?= $row[$this->config['primaryKey']] ?>"
                                                    data-modal-target="edit<?= ucfirst($this->config['table']) ?>Content"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button
                                                    class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                                    data-modal-delete="deleteConfirmModal"
                                                    data-modal-url="delete.php?id=<?= $row[$this->config['primaryKey']] ?>"
                                                    title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($this->totalPages > 1): ?>
                    <div class="pagination-container mt-6"
                        data-current-page="<?= $this->currentPage ?>"
                        data-total-pages="<?= $this->totalPages ?>"
                        data-total-items="<?= $this->total ?>"
                        data-per-page="<?= $this->config['perPage'] ?>">
                        <!-- Pagination will be rendered here by JavaScript -->
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
<?php
    }

    private function renderColumn($row, $column)
    {
        $value = $row[$column['name']] ?? '';

        if (isset($column['format'])) {
            switch ($column['format']) {
                case 'date':
                    return date('M d, Y', strtotime($value));
                case 'datetime':
                    return date('M d, Y H:i', strtotime($value));
                case 'badge':
                    $color = $column['colors'][$value] ?? 'gray';
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-' . $color . '-100 text-' . $color . '-800">' .
                        htmlspecialchars($value) .
                        '</span>';
                case 'custom':
                    return call_user_func($column['callback'], $row);
                default:
                    return htmlspecialchars($value);
            }
        }

        return htmlspecialchars($value);
    }
}
