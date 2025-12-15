<?php
/**
 * Notification Helper Functions
 * Provides easy-to-use functions for creating notifications
 */

require_once('db.php');

/**
 * Create a notification for an admin user
 * 
 * @param int $adminId The admin user ID
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $type Notification type (info, success, warning, error, system)
 * @return bool|int Returns notification ID on success, false on failure
 */
function create_notification($adminId, $title, $message, $type = 'info') {
    global $conn;
    
    // Validate admin ID
    if (empty($adminId) || !is_numeric($adminId)) {
        error_log("Invalid admin ID for notification: " . $adminId);
        return false;
    }
    
    // Validate type
    $allowedTypes = ['info', 'success', 'warning', 'error', 'system'];
    if (!in_array($type, $allowedTypes)) {
        $type = 'info';
    }
    
    // Ensure notifications table exists
    ensure_notifications_table($conn);
    
    // Prepare and execute insert
    $sql = "INSERT INTO notifications (admin_id, title, message, type) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Failed to prepare notification insert: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param('isss', $adminId, $title, $message, $type);
    $ok = $stmt->execute();
    
    if (!$ok) {
        error_log("Failed to insert notification: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $notificationId = $conn->insert_id;
    $stmt->close();
    
    return $notificationId;
}

/**
 * Ensure the notifications table exists
 * Creates the table if it doesn't exist
 */
function ensure_notifications_table($conn) {
    $checkTable = "SHOW TABLES LIKE 'notifications'";
    $result = $conn->query($checkTable);
    
    if ($result->num_rows == 0) {
        $createTable = "CREATE TABLE IF NOT EXISTS `notifications` (
            `notification_id` INT(11) NOT NULL AUTO_INCREMENT,
            `admin_id` INT(11) NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `message` TEXT NOT NULL,
            `type` ENUM('info', 'success', 'warning', 'error', 'system') DEFAULT 'info',
            `is_read` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `read_at` TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`notification_id`),
            INDEX `idx_admin_id` (`admin_id`),
            INDEX `idx_is_read` (`is_read`),
            INDEX `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (!$conn->query($createTable)) {
            error_log("Failed to create notifications table: " . $conn->error);
        }
    }
}

/**
 * Create notification for all admins
 * Useful for system-wide notifications
 * 
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $type Notification type
 * @return int Number of notifications created
 */
function create_notification_for_all_admins($title, $message, $type = 'info') {
    global $conn;
    
    ensure_notifications_table($conn);
    
    // Get all admin IDs
    $sql = "SELECT admin_id FROM admin";
    $result = $conn->query($sql);
    
    if (!$result) {
        error_log("Failed to fetch admin IDs: " . $conn->error);
        return 0;
    }
    
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        if (create_notification($row['admin_id'], $title, $message, $type)) {
            $count++;
        }
    }
    
    return $count;
}

?>
