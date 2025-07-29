<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once '../includes/security.php';
    secure_session_start();
    check_user_type(1); // 1 for sales manager (formerly supervisor)
    require_once '../config/db_connection.php';
    require_once '../includes/functions/inventory_functions.php';

    // Secondary database connection for friend's inventory database
    $servername = "localhost";
    $username_friend = "uumsoftw_miza";
    $password_friend = "Miza@123";
    $database_friend = "uumsoftw_inventory_db";
    
    // Try to connect to friend's database
    $conn_friend = new mysqli($servername, $username_friend, $password_friend, $database_friend);

    // Check friend's database connection
    if ($conn_friend->connect_error) {
        throw new Exception("Friend's database connection failed: " . $conn_friend->connect_error);
    }
    $conn_friend->set_charset("utf8mb4");

    // Enhanced filtering and search
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $stock_filter = isset($_GET['stock']) ? $_GET['stock'] : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';

    // Check if function exists before calling it
    if (!function_exists('fetch_small_inventory_data_from_friend')) {
        throw new Exception("Function fetch_small_inventory_data_from_friend does not exist");
    }

    // Fetch raw materials inventory data from friend's database
    $small_inventory_data = fetch_small_inventory_data_from_friend($conn_friend, $sort);
    
    if ($small_inventory_data === false) {
        throw new Exception("Failed to fetch inventory data from friend's database");
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "<br>File: " . $e->getFile() . "<br>Line: " . $e->getLine());
}

// Apply filters
if (!empty($search) || !empty($stock_filter)) {
    $filtered_data = [];
    foreach ($small_inventory_data as $item) {
        // Search filter
        $match_search = empty($search) ||
            (stripos($item['Ingredient_Name'], $search) !== false) ||
            (stripos((string)$item['Inventory_ID'], $search) !== false);

        // Stock filter
        $match_stock = true;
        if ($stock_filter === 'low') {
            $match_stock = $item['Ingredient_kg'] < 20 && $item['Ingredient_kg'] > 0;
        } else if ($stock_filter === 'out') {
            $match_stock = $item['Ingredient_kg'] <= 0;
        } else if ($stock_filter === 'sufficient') {
            $match_stock = $item['Ingredient_kg'] >= 20;
        }

        if ($match_search && $match_stock) {
            $filtered_data[] = $item;
        }
    }
    $small_inventory_data = $filtered_data;
}

// Calculate statistics
$total_ingredients = count($small_inventory_data);
$low_stock_count = 0;
$out_of_stock_count = 0;
$total_inventory_weight = 0;

foreach ($small_inventory_data as $item) {
    $total_inventory_weight += $item['Ingredient_kg'];
    if ($item['Ingredient_kg'] <= 0) {
        $out_of_stock_count++;
    } else if ($item['Ingredient_kg'] < 20) {
        $low_stock_count++;
    }
}

// Set page title
$page_title = "Raw Materials - Sales Manager";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/main.css">
<!-- Removed fix_sidebar_responsive.css link as styles are now in main.css -->
    <style>
        :root {
            --primary: #0561FC;
            --primary-light: rgba(5, 97, 252, 0.1);
            --primary-dark: #0453d6;
            --secondary: #6c757d;
            --success: #28a745;
            --info: #17a2b8;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
        }

        .materials-card {
            border-radius: 12px;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
            background-color: white;
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .materials-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .border-left-primary {
            border-left: 4px solid var(--primary);
        }

        .border-left-warning {
            border-left: 4px solid var(--warning);
        }

        .border-left-danger {
            border-left: 4px solid var(--danger);
        }

        .inventory-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }

        .materials-table th {
            font-weight: 600;
            color: #495057;
            border-top: none;
            padding: 1rem;
        }

        .materials-table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .materials-table tbody tr {
            transition: background-color 0.2s ease;
        }

        .materials-table tbody tr:hover {
            background-color: rgba(5, 97, 252, 0.03);
        }

        .progress {
            height: 6px;
            border-radius: 3px;
        }

        .filter-section {
            border-radius: 12px;
            background-color: white;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .search-input {
            position: relative;
        }

        .search-input i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .search-input input {
            padding-left: 2.5rem;
            border-radius: 8px;
            border: 1px solid #ced4da;
        }

        .badge {
            padding: 0.5em 0.7em;
            font-weight: 500;
            border-radius: 30px;
        }

        /* For Print View */
        #printView {
            display: none;
        }
        
        @media print {
            #printView {
                display: block;
            }
            
            #printView .print-header {
                text-align: center;
                margin-bottom: 20px;
            }
            
            #printView .print-header h1 {
                font-size: 24px;
                margin-bottom: 5px;
            }
            
            #printView .print-header p {
                font-size: 14px;
                color: #6c757d;
            }
            
            #printView table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            
            #printView th, #printView td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            
            #printView th {
                background-color: #f8f9fa;
                font-weight: bold;
            }
            
            .sidebar-container, 
            .wrapper,
            .main-content > .d-flex:first-child, 
            .filter-section, 
            .card-header .dropdown,
            .btn, 
            .card-footer,
            .inventory-icon,
            .progress {
                display: none !important;
            }
        }

        .badge-sufficient {
            background-color: var(--success);
            color: white;
        }
        
        .badge-low-stock {
            background-color: var(--warning);
            color: #212529;
        }
        
        .badge-out-of-stock {
            background-color: var(--danger);
            color: white;
        }
        
        .back-button {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">  
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800 fw-bold">Raw Materials Inventory</h1>
                    <p class="text-muted">Manage and track ingredients inventory</p>
                </div>
                <div class="text-md-end">
                    <div class="mb-2 text-muted">
                        <i class="bi bi-calendar3"></i> <?php echo date('l, d F Y'); ?>
                    </div>
                </div>
            </div>
            
            <!-- Filter and Search Section -->
            <div class="filter-section mb-4">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="search-input">
                            <i class="bi bi-search"></i>
                            <input type="text" name="search" class="form-control" placeholder="Search by name or ID..."
                                value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="stock" class="form-select">
                            <option value="">All Stock Levels</option>
                            <option value="low" <?php echo $stock_filter === 'low' ? 'selected' : ''; ?>>Low Stock</option>
                            <option value="out" <?php echo $stock_filter === 'out' ? 'selected' : ''; ?>>Out of Stock</option>
                            <option value="sufficient" <?php echo $stock_filter === 'sufficient' ? 'selected' : ''; ?>>Sufficient</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="sort" class="form-select">
                            <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Sort by Name</option>
                            <option value="id" <?php echo $sort === 'id' ? 'selected' : ''; ?>>Sort by ID</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="bi bi-funnel me-1"></i> Filter
                            </button>
                            <a href="sm_materials.php" class="btn btn-outline-secondary flex-fill">
                                <i class="bi bi-x-circle me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Materials Summary Cards -->
            <div class="row mb-4">
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto me-3">
                                    <div class="inventory-icon">
                                        <i class="bi bi-list-task fs-3"></i>
                                    </div>
                                </div>
                                <div class="col me-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Ingredients
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_ingredients; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto me-3">
                                    <div class="inventory-icon">
                                        <i class="bi bi-exclamation-triangle fs-3"></i>
                                    </div>
                                </div>
                                <div class="col me-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Low Stock Items
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $low_stock_count; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto me-3">
                                    <div class="inventory-icon">
                                        <i class="bi bi-truck fs-3"></i>
                                    </div>
                                </div>
                                <div class="col me-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Total Weight
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_inventory_weight, 2); ?> kg</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Materials Table -->
            <div class="materials-card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary">Raw Materials List</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear me-1"></i> Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" id="printMaterialsBtn">
                                <i class="bi bi-printer me-2"></i> Print Inventory
                            </a></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#exportModal">
                                <i class="bi bi-download me-2"></i> Export Data
                            </a></li>
                            <li><a class="dropdown-item" href="sm_inventory.php">
                                <i class="bi bi-box-seam me-2"></i> View Products
                            </a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($small_inventory_data)): ?>
                        <div class="alert alert-info m-3 text-center">
                            No ingredients found matching your search criteria.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table materials-table mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Ingredient Name</th>
                                        <th>Quantity (kg)</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($small_inventory_data as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['Inventory_ID']); ?></td>
                                            <td><?php echo htmlspecialchars($item['Ingredient_Name']); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2">
                                                        <div class="progress-bar <?php
                                                        echo $item['Ingredient_kg'] < 20 ?
                                                            ($item['Ingredient_kg'] <= 0 ? 'bg-danger' : 'bg-warning') :
                                                            'bg-success';
                                                        ?>" style="width: <?php
                                                        echo min(($item['Ingredient_kg'] / 100) * 100, 100);
                                                        ?>%"></div>
                                                    </div>
                                                    <span><?php echo number_format($item['Ingredient_kg'], 2); ?> kg</span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($item['Ingredient_kg'] <= 0): ?>
                                                    <span class="badge badge-out-of-stock">Out of Stock</span>
                                                <?php elseif ($item['Ingredient_kg'] < 20): ?>
                                                    <span class="badge badge-low-stock">Low Stock</span>
                                                <?php else: ?>
                                                    <span class="badge badge-sufficient">Sufficient</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($small_inventory_data)): ?>
                    <div class="card-footer bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Showing <?php echo count($small_inventory_data); ?> ingredients
                            </div>
                            <?php if ($search || $stock_filter): ?>
                                <a href="sm_materials.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Clear Filters
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Print View (Hidden from normal view, only shown when printing) -->
    <div id="printView">
        <div class="print-header">
            <h1>RotiSeri Bakery - Raw Materials Report</h1>
            <p>Generated on: <?php echo date('F j, Y'); ?></p>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ingredient Name</th>
                    <th>Quantity (kg)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($small_inventory_data as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['Inventory_ID']); ?></td>
                        <td><?php echo htmlspecialchars($item['Ingredient_Name']); ?></td>
                        <td><?php echo number_format($item['Ingredient_kg'], 2); ?> kg</td>
                        <td>
                            <?php 
                            if ($item['Ingredient_kg'] <= 0) {
                                echo 'Out of Stock';
                            } elseif ($item['Ingredient_kg'] < 20) {
                                echo 'Low Stock';
                            } else {
                                echo 'Sufficient';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Materials Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Select the format to export materials data:</p>
                    <div class="list-group">
                        <a href="export_materials.php?format=excel&search=<?php echo urlencode($search); ?>&stock_filter=<?php echo urlencode($stock_filter); ?>&sort=<?php echo urlencode($sort); ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Excel Spreadsheet</h6>
                                    <p class="mb-1 small text-muted">Export to Microsoft Excel format</p>
                                </div>
                                <span class="badge bg-primary rounded-pill">.xlsx</span>
                            </div>
                        </a>                        <a href="export_materials.php?format=csv&search=<?php echo urlencode($search); ?>&stock_filter=<?php echo urlencode($stock_filter); ?>&sort=<?php echo urlencode($sort); ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">CSV File</h6>
                                    <p class="mb-1 small text-muted">Export as comma-separated values</p>
                                </div>
                                <span class="badge bg-primary rounded-pill">.csv</span>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Improved Print functionality
        document.getElementById('printMaterialsBtn').addEventListener('click', function (e) {
            e.preventDefault();
            
            // Set a short timeout to ensure the print view is ready
            setTimeout(function() {
                window.print();
            }, 100);
        });

        // Tooltip initialization
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });    });
    </script>
</body>
</html>

<?php
// Close friend's database connection
if (isset($conn_friend)) {
    $conn_friend->close();
}
?>