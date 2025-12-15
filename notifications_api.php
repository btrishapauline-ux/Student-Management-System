<?php
/**
 * API endpoint for admin notifications
 * Handles fetching, marking as read, deleting, and creating notifications
 */

session_start();
require_once('db.php');

header('Content-Type: application/json');

// Helper to send JSON responses
function json_response($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

// Require admin authentication
function require_admin() {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 401);
    }
    return (int)$_SESSION['user_id'];
}

// Check if notifications table exists, create if not
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
        
        $conn->query($createTable);
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === '') {
    json_response(['success' => false, 'message' => 'No action specified'], 400);
}

// Ensure table exists
ensure_notifications_table($conn);

switch ($action) {
    case 'list':
        require_admin();
        list_notifications($conn);
        break;
    case 'unread_count':
        require_admin();
        get_unread_count($conn);
        break;
    case 'mark_read':
        require_admin();
        mark_as_read($conn);
        break;
    case 'mark_all_read':
        require_admin();
        mark_all_as_read($conn);
        break;
    case 'delete':
        require_admin();
        delete_notification($conn);
        break;
    case 'delete_all':
        require_admin();
        delete_all_notifications($conn);
        break;
    case 'create':
        require_admin();
        create_notification($conn);
        break;
    default:
        json_response(['success' => false, 'message' => 'Unknown action'], 400);
}

// ---------- Handlers ----------

function list_notifications($conn) {
    $adminId = (int)$_SESSION['user_id'];
    $limit = (int)($_GET['limit'] ?? 50);
    $offset = (int)($_GET['offset'] ?? 0);
    $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
    
    $sql = "SELECT notification_id, title, message, type, is_read, 
                   created_at, read_at
            FROM notifications 
            WHERE admin_id = ?";
    
    if ($unreadOnly) {
        $sql .= " AND is_read = 0";
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        json_response(['success' => false, 'message' => 'Query failed: ' . $conn->error], 500);
    }
    
    $stmt->bind_param('iii', $adminId, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => (int)$row['notification_id'],
            'title' => $row['title'],
            'message' => $row['message'],
            'type' => $row['type'],
            'isRead' => (bool)$row['is_read'],
            'createdAt' => $row['created_at'],
            'readAt' => $row['read_at'],
            'timeAgo' => time_ago($row['created_at'])
        ];
    }
    
    $stmt->close();
    json_response(['success' => true, 'data' => $notifications]);
}

function get_unread_count($conn) {
    $adminId = (int)$_SESSION['user_id'];
    
    $sql = "SELECT COUNT(*) as count FROM notifications WHERE admin_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        json_response(['success' => false, 'message' => 'Query failed'], 500);
    }
    
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    json_response(['success' => true, 'count' => (int)$row['count']]);
}

function mark_as_read($conn) {
    $adminId = (int)$_SESSION['user_id'];
    $notificationId = (int)($_POST['id'] ?? 0);
    
    if ($notificationId <= 0) {
        json_response(['success' => false, 'message' => 'Invalid notification ID'], 400);
    }
    
    $sql = "UPDATE notifications 
            SET is_read = 1, read_at = NOW() 
            WHERE notification_id = ? AND admin_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        json_response(['success' => false, 'message' => 'Update failed'], 500);
    }
    
    $stmt->bind_param('ii', $notificationId, $adminId);
    $ok = $stmt->execute();
    $stmt->close();
    
    if (!$ok) {
        json_response(['success' => false, 'message' => 'Failed to mark as read'], 500);
    }
    
    json_response(['success' => true]);
}

function mark_all_as_read($conn) {
    $adminId = (int)$_SESSION['user_id'];
    
    $sql = "UPDATE notifications 
            SET is_read = 1, read_at = NOW() 
            WHERE admin_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        json_response(['success' => false, 'message' => 'Update failed'], 500);
    }
    
    $stmt->bind_param('i', $adminId);
    $ok = $stmt->execute();
    $stmt->close();
    
    if (!$ok) {
        json_response(['success' => false, 'message' => 'Failed to mark all as read'], 500);
    }
    
    json_response(['success' => true]);
}

function delete_notification($conn) {
    $adminId = (int)$_SESSION['user_id'];
    $notificationId = (int)($_POST['id'] ?? 0);
    
    if ($notificationId <= 0) {
        json_response(['success' => false, 'message' => 'Invalid notification ID'], 400);
    }
    
    $sql = "DELETE FROM notifications WHERE notification_id = ? AND admin_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        json_response(['success' => false, 'message' => 'Delete failed'], 500);
    }
    
    $stmt->bind_param('ii', $notificationId, $adminId);
    $ok = $stmt->execute();
    $stmt->close();
    
    if (!$ok) {
        json_response(['success' => false, 'message' => 'Failed to delete notification'], 500);
    }
    
    json_response(['success' => true]);
}

function delete_all_notifications($conn) {
    $adminId = (int)$_SESSION['user_id'];
    
    $sql = "DELETE FROM notifications WHERE admin_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        json_response(['success' => false, 'message' => 'Delete failed'], 500);
    }
    
    $stmt->bind_param('i', $adminId);
    $ok = $stmt->execute();
    $stmt->close();
    
    if (!$ok) {
        json_response(['success' => false, 'message' => 'Failed to delete all notifications'], 500);
    }
    
    json_response(['success' => true]);
}

function create_notification($conn) {
    $adminId = (int)$_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $type = trim($_POST['type'] ?? 'info');
    
    // Validate type
    $allowedTypes = ['info', 'success', 'warning', 'error', 'system'];
    if (!in_array($type, $allowedTypes)) {
        $type = 'info';
    }
    
    if (empty($title) || empty($message)) {
        json_response(['success' => false, 'message' => 'Title and message are required'], 400);
    }
    
    $sql = "INSERT INTO notifications (admin_id, title, message, type) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        json_response(['success' => false, 'message' => 'Insert failed'], 500);
    }
    
    $stmt->bind_param('isss', $adminId, $title, $message, $type);
    $ok = $stmt->execute();
    $newId = $ok ? $conn->insert_id : null;
    $stmt->close();
    
    if (!$ok) {
        json_response(['success' => false, 'message' => 'Failed to create notification'], 500);
    }
    
    json_response(['success' => true, 'id' => $newId]);
}

// Helper function to format time ago
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $timestamp);
    }
}

?>
