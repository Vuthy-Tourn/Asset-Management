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

        // Add time filter conditions - MODIFIED SECTION
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
                    // No WHERE condition, just change the order
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

    private function renderFilters()
    {
?>
        <div class="rounded-lg shadow-sm p-4 mb-6 bg-gray-50">
            <form id="filterForm" method="get" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php if (!empty($this->config['filterOptions'])): ?>
                    <?php foreach ($this->config['filterOptions'] as $filterName => $options): ?>
                        <div>
                            <label for="<?= $filterName ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                Filter by <?= ucfirst(str_replace('_', ' ', $filterName)) ?>
                            </label>
                            <select
                                id="<?= $filterName ?>"
                                name="<?= $filterName ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
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

                <!-- Time Filter Dropdown -->
                <div>
                    <label for="time_filter" class="block text-sm font-medium text-gray-700 mb-1">
                        Time Period
                    </label>
                    <select
                        id="time_filter"
                        name="time_filter"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        <?php foreach ($this->config['timeFilterOptions'] as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>"
                                <?= $this->timeFilter === $value ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Hidden fields to maintain state -->
                <?php if (!empty($this->searchTerm)): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($this->searchTerm) ?>">
                <?php endif; ?>
                <input type="hidden" name="page" value="1">
                <button type="submit" class="hidden">Apply Filters</button>
            </form>
        </div>
    <?php
    }

    private function getFilterOptions($filterName)
    {
        // You can customize this method to fetch options from database if needed
        return $this->config['filterOptions'][$filterName];
    }

    private function renderSearch()
    {
    ?>
        <div class="rounded-lg shadow-sm p-4 mb-6">
            <form id="searchForm" method="GET" action="" class="flex flex-col sm:flex-row gap-4 items-center"
                data-live-search="true">
                <div class="relative flex-1 max-w-md">
                    <input
                        type="text"
                        id="searchInput"
                        name="search"
                        value="<?= htmlspecialchars($this->searchTerm) ?>"
                        placeholder="Search <?= $this->config['table'] ?>..."
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
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

        $this->renderSearch();
        $this->renderFilters();
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
                            <?php foreach ($this->data as $row): ?>
                                <tr>
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
                    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <?php if ($this->currentPage > 1): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $this->currentPage - 1])) ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Previous
                                </a>
                            <?php endif; ?>
                            <?php if ($this->currentPage < $this->totalPages): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $this->currentPage + 1])) ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Next
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing <span class="font-medium"><?= (($this->currentPage - 1) * $this->config['perPage']) + 1 ?></span> to
                                    <span class="font-medium"><?= min($this->currentPage * $this->config['perPage'], $this->total) ?></span> of
                                    <span class="font-medium"><?= $this->total ?></span> results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <?php if ($this->currentPage > 1): ?>
                                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $this->currentPage - 1])) ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php
                                    $start = max(1, $this->currentPage - 2);
                                    $end = min($this->totalPages, $this->currentPage + 2);

                                    for ($i = $start; $i <= $end; $i++): ?>
                                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                                            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?= $i == $this->currentPage ? 'z-10 bg-purple-50 border-purple-500 text-purple-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?>">
                                            <?= $i ?>
                                        </a>
                                    <?php endfor; ?>

                                    <?php if ($this->currentPage < $this->totalPages): ?>
                                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $this->currentPage + 1])) ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </nav>
                            </div>
                        </div>
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
