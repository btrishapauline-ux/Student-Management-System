<?php
/**
 * Script to create the notifications table
 * Run this file once to set up the notifications table in your database
 */

require_once('db.php');

// SQL to create notifications table
$sql = "CREATE TABLE IF NOT EXISTS `notifications` (
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
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`admin_id`) REFERENCES `admin`(`admin_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    if ($conn->query($sql)) {
        echo "✓ Notifications table created successfully!<br>";
        
        // Insert some sample notifications for testing
        $adminId = 1; // Change this to your admin ID if needed
        $sampleNotifications = [
            [
                'title' => 'Welcome to Admin Dashboard',
                'message' => 'Your admin account has been successfully set up. You can now manage student records.',
                'type' => 'success'
            ],
            [
                'title' => 'System Update Available',
                'message' => 'A new version of the Student Management System is available. Please check for updates.',
                'type' => 'info'
            ],
            [
                'title' => 'New Student Registration',
                'message' => 'A new student has registered in the system. Review their information.',
                'type' => 'info'
            ]
        ];
        
        $insertSql = "INSERT INTO notifications (admin_id, title, message, type) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertSql);
        
        foreach ($sampleNotifications as $notif) {
            $stmt->bind_param('isss', $adminId, $notif['title'], $notif['message'], $notif['type']);
            $stmt->execute();
        }
        
        $stmt->close();
        echo "✓ Sample notifications inserted!<br>";
        echo "<br><a href='admin.php'>Go to Admin Dashboard</a>";
    } else {
        echo "Error creating table: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>
