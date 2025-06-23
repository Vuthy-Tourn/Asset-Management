<?php
require_once '../components/config/db.php';
require_once '../components/flash.php';

class DataTable
{
    private $conn;
    private $config = [];
    private $data = [];
    private $total = 0;
    private $totalPages = 0;
    private $currentPage = 1;
    private $searchTerm = '';

    public function __construct($dbConnection, $config = [])
    {
        $this->conn = $dbConnection;
        $this->config = array_merge([
            'table' => '',
            'primaryKey' => 'id',
            'columns' => [],
            'joins' => [],
            'searchable' => [],
            'defaultOrder' => 'created_at DESC',
            'perPage' => 10,
            'actions' => true,
            'emptyMessage' => 'No records found',
            'emptySearchMessage' => 'No results found for your search',
            'addButton' => false,
            'addButtonText' => 'Add New',
            'addButtonModal' => '',
            'customFilters' => [],
        ], $config);
    }

    public function render()
    {
        $this->handleFlashMessage();
        $this->processRequest();
        $this->fetchData();
        $this->renderSearchAndFilters();
        $this->renderTable();
    }

    private function handleFlashMessage()
    {
        $flash = flash();
        if ($flash) {
            require_once '../components/toast.php';
            showToast($flash['message'], $flash['type']);
        }
    }

    private function processRequest()
    {
        $this->currentPage = max(1, (int)($_GET['page'] ?? 1));
        $this->searchTerm = trim($_GET['search'] ?? '');
    }

    private function fetchData()
    {
        $offset = ($this->currentPage - 1) * $this->config['perPage'];
        $searchCondition = '';
        $searchParams = [];

        // Build search condition if search term exists
        if (!empty($this->searchTerm) && !empty($this->config['searchable'])) {
            $searchParts = [];
            foreach ($this->config['searchable'] as $column) {
                $searchParts[] = "$column LIKE ?";
                $searchParams[] = '%' . $this->searchTerm . '%';
            }
            $searchCondition = 'WHERE ' . implode(' OR ', $searchParts);
        }

        // Add custom filters if any
        foreach ($this->config['customFilters'] as $filter => $value) {
            if (isset($_GET[$filter]) && !empty($_GET[$filter])) {
                $searchCondition .= empty($searchCondition) ? 'WHERE ' : ' AND ';
                $searchCondition .= "$value = ?";
                $searchParams[] = $_GET[$filter];
            }
        }

        try {
            // Get total count
            $countSql = "SELECT COUNT(*) FROM {$this->config['table']}";
            if (!empty($this->config['joins'])) {
                foreach ($this->config['joins'] as $join) {
                    $countSql .= " $join";
                }
            }
            $countSql .= " $searchCondition";

            $countStmt = $this->conn->prepare($countSql);
            if (!empty($searchParams)) {
                $countStmt->bind_param(str_repeat('s', count($searchParams)), ...$searchParams);
            }
            $countStmt->execute();
            $this->total = $countStmt->get_result()->fetch_row()[0];
            $this->totalPages = ceil($this->total / $this->config['perPage']);

            // Get data
            $sql = "SELECT {$this->config['table']}.*";
            if (!empty($this->config['joins'])) {
                foreach ($this->config['joins'] as $join) {
                    // Extract joined table name for column selection
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

            $sql .= " $searchCondition";
            $sql .= " ORDER BY {$this->config['defaultOrder']}";
            $sql .= " LIMIT ? OFFSET ?";

            $stmt = $this->conn->prepare($sql);
            $params = array_merge($searchParams, [$this->config['perPage'], $offset]);
            $types = str_repeat('s', count($searchParams)) . 'ii';
            $stmt->bind_param($types, ...$params);
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

    private function renderSearchAndFilters()
    {
?>
        <div class="rounded-lg shadow-sm p-4 mb-6">
            <form id="searchForm" class="flex flex-col sm:flex-row gap-4 items-center"
                data-live-search="true"
                data-table="<?= htmlspecialchars($this->config['table']) ?>">
                <div class="relative flex-1 max-w-md">
                    <input
                        type="text"
                        id="searchInput"
                        name="search"
                        value="<?= htmlspecialchars($this->searchTerm) ?>"
                        placeholder="Search <?= $this->config['table'] ?>..."
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                        data-live-search-input>
                    <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
                </div>
                <?php if ($this->config['addButton']): ?>
                    <button
                        type="button"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg"
                        data-modal-fetch="<?= $this->config['addButtonModal'] ?>"
                        data-modal-url="create.php"
                        data-modal-target="<?= $this->config['addButtonModal'] ?>Content">
                        <i class="fas fa-plus mr-2"></i><?= $this->config['addButtonText'] ?>
                    </button>
                <?php endif; ?>
            </form>
        </div>
    <?php
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
                    // Handle empty/null values and normalize case
                    $displayValue = empty($value) ? 'Uncategorized' : trim($value);
                    $searchValue = strtolower($displayValue);

                    // Find matching color - check exact match first, then case-insensitive
                    $color = null;
                    foreach ($column['colors'] as $key => $val) {
                        if (strtolower($key) === $searchValue) {
                            $color = $val;
                            break;
                        }
                    }

                    // Fallback to default if no match found
                    $color = $color ?? ($column['default_color'] ?? 'gray');

                    // Support both simple color names and full class strings
                    if (is_string($color) && strpos($color, 'bg-') !== 0) {
                        $color = "bg-{$color}-100 text-{$color}-800 border-{$color}-300";
                    }

                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $color . ' border">' .
                        htmlspecialchars($displayValue) .
                        '</span>';
                case 'truncate':
                    $length = $column['length'] ?? 50;
                    return htmlspecialchars(substr($value, 0, $length) . (strlen($value) > $length ? '...' : ''));
                case 'custom':
                    return call_user_func($column['callback'], $row);
                default:
                    return htmlspecialchars($value);
            }
        }

        return htmlspecialchars($value);
    }
}
