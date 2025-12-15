<?php
/**
 * Test script to verify notification system is working
 * Run this to check if notifications can be created and retrieved
 */

session_start();
require_once('db.php');
require_once('notification_helper.php');

// Check if notifications table exists
$checkTable = "SHOW TABLES LIKE 'notifications'";
$result = $conn->query($checkTable);

if ($result->num_rows == 0) {
    echo "<h2>❌ Notifications table does not exist!</h2>";
    echo "<p>Please run <a href='create_notifications_table.php'>create_notifications_table.php</a> first.</p>";
    exit();
}

echo "<h2>✅ Notifications table exists</h2>";

// Check if we have an admin user
$adminCheck = "SELECT admin_id FROM admin LIMIT 1";
$adminResult = $conn->query($adminCheck);

if ($adminResult->num_rows == 0) {
    echo "<h2>❌ No admin users found!</h2>";
    echo "<p>You need at least one admin user to test notifications.</p>";
    exit();
}

$adminRow = $adminResult->fetch_assoc();
$testAdminId = $adminRow['admin_id'];

echo "<h2>✅ Admin user found (ID: {$testAdminId})</h2>";

// Test creating a notification
echo "<h3>Testing notification creation...</h3>";

$testNotification = create_notification(
    $testAdminId,
    'Test Notification',
    'This is a test notification to verify the system is working correctly.',
    'success'
);

if ($testNotification) {
    echo "<p>✅ Notification created successfully! (ID: {$testNotification})</p>";
} else {
    echo "<p>❌ Failed to create notification. Check error logs.</p>";
    exit();
}

// Test retrieving notifications
echo "<h3>Testing notification retrieval...</h3>";

$getNotifications = "SELECT * FROM notifications WHERE admin_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($getNotifications);
$stmt->bind_param('i', $testAdminId);
$stmt->execute();
$notifications = $stmt->get_result();

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Title</th><th>Message</th><th>Type</th><th>Read</th><th>Created</th></tr>";

$count = 0;
while ($row = $notifications->fetch_assoc()) {
    $count++;
    echo "<tr>";
    echo "<td>{$row['notification_id']}</td>";
    echo "<td>{$row['title']}</td>";
    echo "<td>" . htmlspecialchars(substr($row['message'], 0, 50)) . "...</td>";
    echo "<td>{$row['type']}</td>";
    echo "<td>" . ($row['is_read'] ? 'Yes' : 'No') . "</td>";
    echo "<td>{$row['created_at']}</td>";
    echo "</tr>";
}

echo "</table>";

if ($count > 0) {
    echo "<p>✅ Found {$count} notification(s) for admin ID {$testAdminId}</p>";
} else {
    echo "<p>⚠️ No notifications found for admin ID {$testAdminId}</p>";
}

// Test API endpoint
echo "<h3>Testing API endpoint...</h3>";
echo "<p>Try accessing: <a href='notifications_api.php?action=list' target='_blank'>notifications_api.php?action=list</a></p>";
echo "<p>Or check unread count: <a href='notifications_api.php?action=unread_count' target='_blank'>notifications_api.php?action=unread_count</a></p>";

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>If notifications are working, try creating/editing a student account from the admin panel</li>";
echo "<li>Check the notification bell icon in the admin dashboard</li>";
echo "<li>If you still don't see notifications, check the browser console for JavaScript errors</li>";
echo "</ol>";

$stmt->close();
?>
