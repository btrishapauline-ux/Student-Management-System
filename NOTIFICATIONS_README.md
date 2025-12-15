# Notification System for Admin Dashboard

## Overview
A comprehensive notification system has been implemented for the admin pages (`admin.php` and `admin_profile.php`). The system includes real-time notifications, unread badges, and full CRUD operations.

## Features
- ✅ Real-time notification badge showing unread count
- ✅ Notification dropdown with list of all notifications
- ✅ Mark notifications as read (individual or all)
- ✅ Delete notifications (individual or all)
- ✅ Auto-refresh every 30 seconds
- ✅ Different notification types (info, success, warning, error, system)
- ✅ Responsive design with dark mode support
- ✅ Time-ago formatting for notification timestamps

## Setup Instructions

### 1. Create the Database Table
Run the setup script once to create the notifications table:
```
http://localhost/Student-Management-System/create_notifications_table.php
```

Or manually create the table using the SQL in `create_notifications_table.php`.

### 2. Files Created/Modified

**New Files:**
- `notifications_api.php` - API endpoint for notification operations
- `assets/js/notifications.js` - JavaScript for notification functionality
- `assets/css/notifications.css` - Styles for notification UI
- `create_notifications_table.php` - Database setup script

**Modified Files:**
- `admin.php` - Added notification UI components
- `admin_profile.php` - Added notification UI components

### 3. API Endpoints

The notification API (`notifications_api.php`) supports the following actions:

- `GET ?action=list` - Get list of notifications
- `GET ?action=unread_count` - Get count of unread notifications
- `POST action=mark_read&id={id}` - Mark a notification as read
- `POST action=mark_all_read` - Mark all notifications as read
- `POST action=delete&id={id}` - Delete a notification
- `POST action=delete_all` - Delete all notifications
- `POST action=create&title={title}&message={message}&type={type}` - Create a new notification

### 4. Usage

The notification system is automatically initialized when the admin pages load. The notification bell icon appears in the header next to the profile dropdown.

**Creating Notifications Programmatically:**

```php
// Example: Create a notification when a new student is added
$formData = new FormData();
$formData->append('action', 'create');
$formData->append('title', 'New Student Registered');
$formData->append('message', 'A new student has been added to the system.');
$formData->append('type', 'info');

$response = file_get_contents('notifications_api.php', false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'content' => http_build_query([
            'action' => 'create',
            'title' => 'New Student Registered',
            'message' => 'A new student has been added to the system.',
            'type' => 'info'
        ])
    ]
]));
```

Or using JavaScript:
```javascript
async function createNotification(title, message, type = 'info') {
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('title', title);
    formData.append('message', message);
    formData.append('type', type);
    
    const response = await fetch('notifications_api.php', {
        method: 'POST',
        body: formData
    });
    
    return await response.json();
}
```

### 5. Notification Types

- `info` - General information (blue)
- `success` - Success messages (green)
- `warning` - Warning messages (orange)
- `error` - Error messages (red)
- `system` - System notifications (purple)

### 6. Customization

**Change Polling Interval:**
Edit `assets/js/notifications.js` and modify the `pollIntervalMs` property:
```javascript
this.pollIntervalMs = 30000; // Change to desired milliseconds
```

**Change Notification Limit:**
Edit the `loadNotifications()` function in `notifications.js`:
```javascript
const response = await fetch(`${this.apiUrl}?action=list&limit=20`);
```

**Styling:**
All styles are in `assets/css/notifications.css`. The notification system supports dark mode automatically.

## Testing

1. Run `create_notifications_table.php` to set up the database and create sample notifications
2. Log in as admin and navigate to `admin.php` or `admin_profile.php`
3. Click the bell icon in the header to see notifications
4. Test marking notifications as read
5. Test deleting notifications

## Notes

- The notification system automatically creates the database table if it doesn't exist
- Notifications are scoped to individual admin users
- The system polls for new notifications every 30 seconds
- Polling stops when the page is hidden (tab not active) to save resources
