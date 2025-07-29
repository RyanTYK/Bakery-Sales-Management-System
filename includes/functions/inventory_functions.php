<?php
require_once __DIR__ . '/../../config/db_connection.php';

/**
 * Fetch inventory data
 */
function fetch_inventory_data($conn) {
    try {
        $inventory_query = $conn->query("SELECT id, product_name, stock_level, unit_price, category FROM inventory WHERE status = 'active'");
        if (!$inventory_query) {
            error_log("SQL Error in fetch_inventory_data: " . $conn->error);
            return array();
        }
        $result = $inventory_query->fetch_all(MYSQLI_ASSOC);
        return $result ? $result : array();
    } catch (Exception $e) {
        error_log("Exception in fetch_inventory_data: " . $e->getMessage());
        return array();
    }
}

/**
 * Fetch raw materials inventory data
 */
function fetch_small_inventory_data($conn, $sort = 'name') {
    // Use switch instead of match for better PHP compatibility
    switch($sort) {
        case 'id':
            $order_by = 'Inventory_ID';
            break;
        case 'name':
            $order_by = 'Ingredient_Name';
            break;
        default:
            $order_by = 'Ingredient_Name';
            break;
    }
    
    $query = "SELECT * FROM small_inventory ORDER BY {$order_by}";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return array();
    }
    
    $small_inventory_data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $small_inventory_data[] = $row;
    }
    
    return $small_inventory_data;
}

/**
 * Fetch raw materials inventory data from friend's database
 */
function fetch_small_inventory_data_from_friend($conn_friend, $sort = 'name') {
    // Use switch instead of match for better PHP compatibility
    switch($sort) {
        case 'id':
            $order_by = 'product_id';
            break;
        case 'name':
            $order_by = 'product_name';
            break;
        default:
            $order_by = 'product_name';
            break;
    }
    
    // Map friend's database columns to expected format
    $query = "SELECT 
                product_id as Inventory_ID,
                product_name as Ingredient_Name,
                stock_quantity as Ingredient_kg,
                unit_price,
                description,
                reorder_threshold,
                last_updated
              FROM products 
              ORDER BY {$order_by}";
    
    $result = mysqli_query($conn_friend, $query);
    
    if (!$result) {
        return array();
    }
    
    $small_inventory_data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $small_inventory_data[] = $row;
    }
    
    return $small_inventory_data;
}

/**
 * Fetch inventory by category
 */
function fetch_inventory_by_category($conn) {
    $stmt = $conn->prepare("SELECT * FROM inventory WHERE status = 'active' ORDER BY category, product_name");
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Group products by category
    $categorized_products = [];
    foreach ($products as $product) {
        $categorized_products[$product['category']][] = $product;
    }    
    return $categorized_products;
}

/**
 * Fetch inventory data from friend's database
 */
function fetch_inventory_data_from_friend($conn_friend) {
    try {
        // Fetch data from friend's database using ingredient_stock_status table
        $sql = "
            SELECT 
                ingredient_id as id, 
                ingredient_name as product_name, 
                available_stock as stock_level, 
                COALESCE(reorder_threshold * 2, 10) as unit_price,
                'Ingredients' as category 
            FROM ingredient_stock_status 
            WHERE available_stock IS NOT NULL
        ";
        
        $inventory_query = $conn_friend->query($sql);
        
        if (!$inventory_query) {
            error_log("SQL Error in fetch_inventory_data_from_friend: " . $conn_friend->error);
            return array();
        }
        
        $result = $inventory_query->fetch_all(MYSQLI_ASSOC);
        return $result ? $result : array();
        
    } catch (Exception $e) {
        error_log("Exception in fetch_inventory_data_from_friend: " . $e->getMessage());
        return array();
    }
}

/**
 * Update product stock level
 */
function update_product_stock($conn, $product_id, $new_stock) {
    $stmt = $conn->prepare("UPDATE inventory SET stock_level = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_stock, $product_id);
    return $stmt->execute();
}
?>