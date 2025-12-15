<?php
// Basic session + DB setup
session_start();
require_once('db.php');

// Redirect if not logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

$adminId = (int)$_SESSION['user_id'];

// Initialize $fullName early to avoid undefined variable warning
$fullName = 'Administrator';

// Handle profile image upload
$uploadMessage = '';
$uploadSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    // Check if profile_image column exists, create if it doesn't
    $checkColumnSql = "SHOW COLUMNS FROM admin LIKE 'profile_image'";
    $checkResult = $conn->query($checkColumnSql);
    $columnExists = ($checkResult && $checkResult->num_rows > 0);
    
    // Create column if it doesn't exist
    if (!$columnExists) {
        $createColumnSql = "ALTER TABLE admin ADD COLUMN profile_image LONGTEXT";
        $conn->query($createColumnSql);
        $columnExists = true;
    }
    
    if ($_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $fileType = $_FILES['profile_image']['type'];
        $fileSize = $_FILES['profile_image']['size'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Validate file size
        if ($fileSize > $maxSize) {
            $uploadMessage = 'File size exceeds 5MB limit.';
        } elseif (in_array($fileType, $allowedTypes)) {
            // Read image file and convert to base64
            $imageData = file_get_contents($_FILES['profile_image']['tmp_name']);
            $imageBase64 = base64_encode($imageData);
            
            // Update database with base64 encoded image
            $updateSql = "UPDATE admin SET profile_image = ? WHERE admin_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            if ($updateStmt) {
                $updateStmt->bind_param('si', $imageBase64, $adminId);
                if ($updateStmt->execute()) {
                    $uploadSuccess = true;
                    $uploadMessage = 'Profile picture updated successfully!';
                    // Redirect to avoid resubmission on refresh
                    header('Location: admin_profile.php?upload=success');
                    exit();
                } else {
                    $uploadMessage = 'Failed to update profile picture.';
                }
                $updateStmt->close();
            } else {
                $uploadMessage = 'Database error occurred.';
            }
        } else {
            $uploadMessage = 'Invalid file type. Please upload JPG, PNG, or GIF images only.';
        }
    } elseif ($_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadMessage = 'Error uploading file. Please try again.';
    }
}

// Load current admin info
$admin = [
    'full_name' => '',
    'username' => '',
    'email' => '',
    'contact' => '',
    'department' => '',
    'position' => '',
    'profile_image' => ''
];

// Check if profile_image column exists
$checkColumnSql = "SHOW COLUMNS FROM admin LIKE 'profile_image'";
$columnExists = false;
$checkResult = $conn->query($checkColumnSql);
if ($checkResult && $checkResult->num_rows > 0) {
    $columnExists = true;
}

// Check which columns exist in the admin table (whitelist approach for safety)
$allowedColumns = ['full_name', 'email', 'contact', 'department', 'position'];
$existingColumns = [];

foreach ($allowedColumns as $col) {
    // SHOW COLUMNS doesn't support prepared statements, but we're using a whitelist so it's safe
    $escapedCol = $conn->real_escape_string($col);
    $checkColSql = "SHOW COLUMNS FROM admin LIKE '{$escapedCol}'";
    $checkColResult = $conn->query($checkColSql);
    if ($checkColResult && $checkColResult->num_rows > 0) {
        $existingColumns[] = $col;
    }
}

if ($columnExists) {
    $existingColumns[] = 'profile_image';
}

// Ensure we have at least full_name
if (empty($existingColumns) || !in_array('full_name', $existingColumns)) {
    $existingColumns = ['full_name'];
}

// Build SQL query with only existing columns
$selectFields = implode(', ', array_map(function($col) {
    return "a.`{$col}`";
}, $existingColumns));

// Always try to get username from admin_login
$sql = "SELECT {$selectFields}, al.username
        FROM admin a
        LEFT JOIN admin_login al ON al.admin_id = a.admin_id
        WHERE a.admin_id = ?";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param('i', $adminId);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $admin['full_name'] = $row['full_name'] ?? '';
            $admin['username'] = $row['username'] ?? '';
            $admin['email'] = $row['email'] ?? '';
            $admin['contact'] = $row['contact'] ?? '';
            $admin['department'] = $row['department'] ?? '';
            $admin['position'] = $row['position'] ?? '';
            $admin['profile_image'] = ($columnExists && isset($row['profile_image'])) ? $row['profile_image'] : '';
            
            // Set fullName after loading data
            $fullName = htmlspecialchars(trim($admin['full_name']));
            if (empty(trim($fullName))) {
                $fullName = 'Administrator';
            }
        }
    }
    $stmt->close();
} else {
    // Fallback: try a simpler query with just full_name
    $fallbackSql = "SELECT a.full_name, al.username FROM admin a LEFT JOIN admin_login al ON al.admin_id = a.admin_id WHERE a.admin_id = ?";
    $fallbackStmt = $conn->prepare($fallbackSql);
    if ($fallbackStmt) {
        $fallbackStmt->bind_param('i', $adminId);
        if ($fallbackStmt->execute()) {
            $fallbackResult = $fallbackStmt->get_result();
            if ($row = $fallbackResult->fetch_assoc()) {
                $admin['full_name'] = $row['full_name'] ?? '';
                $admin['username'] = $row['username'] ?? '';
                $fullName = htmlspecialchars(trim($admin['full_name']));
                if (empty(trim($fullName))) {
                    $fullName = 'Administrator';
                }
            }
        }
        $fallbackStmt->close();
    }
}

// Helper function to get profile image src
function getProfileImageSrc($profileImage) {
    if (!empty($profileImage)) {
        return 'data:image/jpeg;base64,' . $profileImage;
    }
    // Fallback to a default avatar or use a placeholder
    if (file_exists('assets/img/admin-avatar.jpg')) {
        return 'assets/img/admin-avatar.jpg';
    }
    // Use a data URI for a simple placeholder if image doesn't exist
    return 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="150" height="150"><rect fill="#ddd" width="150" height="150"/><text fill="#999" font-family="Arial" font-size="50" x="50%" y="50%" text-anchor="middle" dominant-baseline="middle">Admin</text></svg>');
}
$profileImageSrc = getProfileImageSrc($admin['profile_image']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Admin Profile | Bicol University</title>
  <meta name="description" content="Admin Profile Dashboard">
  <meta name="keywords" content="admin, profile, dashboard, university">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Jost:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
  
  <!-- Student Profile CSS (reused for admin) -->
  <link href="assets/css/student-profile.css" rel="stylesheet">
  
  <!-- Dark Mode CSS -->
  <link href="assets/css/dark-mode.css" rel="stylesheet">
  
  <!-- Notifications CSS -->
  <link href="assets/css/notifications.css" rel="stylesheet">
</head>

<body class="student-profile-page admin-profile-page">

  <!-- Header -->
  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <!-- Logo -->
      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <img src="assets/img/logo.png" alt="Bicol University Logo" style="height: 40px; margin-right: 10px;">
        <h1 class="sitename">BICOL UNIVERSITY</h1>
      </a>

      <!-- Navigation Menu -->
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="admin.php">Dashboard</a></li>
          <li><a href="admin_profile.php" class="active">Profile</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <!-- Notification Bell Container -->
      <div class="notification-bell-container">
        <div class="notification-bell" id="notificationBell">
          <i class="bi bi-bell"></i>
          <span class="notification-badge" id="notificationBadge">0</span>
        </div>

        <!-- Notification Dropdown -->
        <div class="notification-dropdown" id="notificationDropdown">
        <div class="notification-dropdown-header">
          <h5>Notifications</h5>
          <div class="notification-dropdown-actions">
            <button class="btn-notification-header" id="markAllReadBtn" title="Mark all as read">
              <i class="bi bi-check-all"></i>
            </button>
            <button class="btn-notification-header" id="clearAllNotificationsBtn" title="Clear all">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
        <div class="notification-list" id="notificationList">
          <div class="notification-empty">
            <i class="bi bi-bell-slash"></i>
            <p>Loading notifications...</p>
          </div>
        </div>
        <div class="notification-dropdown-footer">
          <button class="btn-view-all" onclick="notificationSystem.loadNotifications()">
            Refresh
          </button>
        </div>
      </div>
      </div>

      <!-- Profile Dropdown -->
      <div class="profile-dropdown">
        <div class="profile-trigger" id="profileTrigger">
          <div class="profile-avatar">
            <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Admin Avatar" id="profileAvatar">
          </div>
          <div class="profile-info">
            <span class="profile-name" id="profileName"><?php echo $fullName ?: 'Administrator'; ?></span>
            <span class="profile-role">Administrator</span>
          </div>
          <i class="bi bi-chevron-down"></i>
        </div>
        
        <div class="profile-dropdown-menu" id="profileDropdown">
          <div class="dropdown-header">
            <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Admin Avatar" id="dropdownAvatar">
            <div>
              <h6 id="dropdownName"><?php echo $fullName ?: 'Administrator'; ?></h6>
              <span><?php echo htmlspecialchars($admin['position'] ?: 'System Administrator'); ?></span>
            </div>
          </div>
          <div class="dropdown-divider"></div>
          <a href="admin_profile.php" class="dropdown-item">
            <i class="bi bi-person"></i> My Profile
          </a>
          <a href="admin.php" class="dropdown-item">
            <i class="bi bi-speedometer2"></i> Dashboard
          </a>
          <div class="dropdown-divider"></div>
          <a href="logout.php" class="dropdown-item">
            <i class="bi bi-box-arrow-right"></i> Logout
          </a>
        </div>
      </div>

    </div>
  </header>

  <!-- Main Content -->
  <main class="main">
    <!-- Profile Content Section -->
    <section class="profile-content section">
      <div class="container">
        <!-- Upload Success/Error Message -->
        <?php if (isset($_GET['upload']) && $_GET['upload'] === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle"></i> Profile picture updated successfully!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        <?php if (!empty($uploadMessage) && !$uploadSuccess): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($uploadMessage); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Profile Header Card -->
        <div class="profile-header-card">
          <div class="profile-avatar-section">
            <!-- Circular Avatar Container -->
            <div class="profile-avatar-container">
              <div class="avatar-wrapper">
                <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="<?php echo htmlspecialchars($fullName); ?>" class="profile-main-avatar" id="mainProfileAvatar">
                <button class="avatar-upload-btn" id="changeAvatarBtn" title="Change Profile Picture">
                  <i class="bi bi-camera"></i>
                </button>
              </div>
            </div>
            
            <!-- Profile Info -->
            <div class="profile-info-main">
              <h1 class="student-name" id="mainProfileName"><?php echo $fullName ?: 'Administrator'; ?></h1>
              <p class="student-id">Admin ID: <?php echo htmlspecialchars($admin['username'] ?: 'N/A'); ?></p>
              <div class="program-badges">
                <?php if ($admin['position']): ?>
                <span class="program-badge"><?php echo htmlspecialchars($admin['position']); ?></span>
                <?php endif; ?>
                <?php if ($admin['department']): ?>
                <span class="year-badge"><?php echo htmlspecialchars($admin['department']); ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>
          
          <!-- Admin Stats Section -->
          <div class="progress-section">
            <h4>System Overview</h4>
            <div class="progress-bar-container">
              <div class="progress-bar" style="width: 85%;"></div>
            </div>
            <div class="progress-text">
              <span>85% System Health</span>
              <span>All systems operational</span>
            </div>
          </div>
        </div>

        <div class="row">
          <!-- Main Content Column -->
          <div class="col-lg-8">
            
            <!-- Tabs Navigation -->
            <div class="profile-tabs-nav">
              <ul class="nav nav-tabs-custom" id="profileTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button">
                    <i class="bi bi-person-vcard"></i> Personal Info
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact-info" type="button">
                    <i class="bi bi-telephone"></i> Contact
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin-info" type="button">
                    <i class="bi bi-shield-check"></i> Admin Details
                  </button>
                </li>
              </ul>
            </div>
            
            <!-- Tab Content -->
            <div class="tab-content" id="profileTabContent">
              
              <!-- Personal Information Tab -->
              <div class="tab-pane fade show active" id="personal" role="tabpanel">
                <div class="info-grid">
                  <div class="info-card">
                    <h5>Personal Information</h5>
                    <div class="info-item">
                      <label>FULL NAME</label>
                      <p id="infoFullName"><?php echo $fullName ?: 'Administrator'; ?></p>
                    </div>
                    <div class="info-item">
                      <label>ADMIN ID</label>
                      <p id="infoAdminId"><?php echo htmlspecialchars($admin['username'] ?: '—'); ?></p>
                    </div>
                    <div class="info-item">
                      <label>POSITION</label>
                      <p id="infoPosition"><?php echo htmlspecialchars($admin['position'] ?: '—'); ?></p>
                    </div>
                    <div class="info-item">
                      <label>DEPARTMENT</label>
                      <p id="infoDepartment"><?php echo htmlspecialchars($admin['department'] ?: '—'); ?></p>
                    </div>
                  </div>
                  
                  <div class="info-card">
                    <h5>Account Information</h5>
                    <div class="info-item">
                      <label>EMAIL ADDRESS</label>
                      <p id="infoEmail"><?php echo htmlspecialchars($admin['email'] ?: '—'); ?></p>
                    </div>
                    <div class="info-item">
                      <label>PHONE NUMBER</label>
                      <p id="infoPhone"><?php echo htmlspecialchars($admin['contact'] ?: '—'); ?></p>
                    </div>
                    <div class="info-item">
                      <label>ACCOUNT TYPE</label>
                      <p class="text-success">Administrator ✓</p>
                    </div>
                    <div class="info-item">
                      <label>ACCOUNT STATUS</label>
                      <p class="text-success">Active ✓</p>
                    </div>
                  </div>
                </div>
                
                <!-- Recent Activities -->
                <div class="recent-activities">
                  <h5>Recent Activities</h5>
                  <div class="activity-list">
                    <div class="activity-item">
                      <div class="activity-icon blue">
                        <i class="bi bi-person-plus"></i>
                      </div>
                      <div class="activity-content">
                        <h6>Added new student record</h6>
                        <small>Today, 10:30 AM</small>
                      </div>
                    </div>
                    
                    <div class="activity-item">
                      <div class="activity-icon green">
                        <i class="bi bi-pencil"></i>
                      </div>
                      <div class="activity-content">
                        <h6>Updated student information</h6>
                        <small>Yesterday, 3:45 PM</small>
                      </div>
                    </div>
                    
                    <div class="activity-item">
                      <div class="activity-icon orange">
                        <i class="bi bi-shield-check"></i>
                      </div>
                      <div class="activity-content">
                        <h6>System backup completed</h6>
                        <small>March 25, 2024 • 2 days ago</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Contact Information Tab -->
              <div class="tab-pane fade" id="contact-info" role="tabpanel">
                <div class="info-grid">
                  <div class="info-card">
                    <h5>Contact Information</h5>
                    <div class="info-item">
                      <label>EMAIL ADDRESS</label>
                      <p id="infoEmail"><?php echo htmlspecialchars($admin['email'] ?: '—'); ?></p>
                    </div>
                    <div class="info-item">
                      <label>PHONE NUMBER</label>
                      <p id="infoPhone"><?php echo htmlspecialchars($admin['contact'] ?: '—'); ?></p>
                    </div>
                  </div>
                  
                  <div class="info-card">
                    <h5>Office Information</h5>
                    <div class="info-item">
                      <label>DEPARTMENT</label>
                      <p id="infoDepartment"><?php echo htmlspecialchars($admin['department'] ?: '—'); ?></p>
                    </div>
                    <div class="info-item">
                      <label>POSITION</label>
                      <p id="infoPosition"><?php echo htmlspecialchars($admin['position'] ?: '—'); ?></p>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Admin Details Tab -->
              <div class="tab-pane fade" id="admin-info" role="tabpanel">
                <div class="info-grid">
                  <div class="info-card">
                    <h5>Administrative Information</h5>
                    <div class="info-item">
                      <label>ADMIN ID</label>
                      <p><?php echo htmlspecialchars($admin['username'] ?: '—'); ?></p>
                    </div>
                    <div class="info-item">
                      <label>POSITION</label>
                      <p><?php echo htmlspecialchars($admin['position'] ?: '—'); ?></p>
                    </div>
                    <div class="info-item">
                      <label>DEPARTMENT</label>
                      <p><?php echo htmlspecialchars($admin['department'] ?: '—'); ?></p>
                    </div>
                    <div class="info-item">
                      <label>ACCESS LEVEL</label>
                      <p class="text-success">Full Administrator Access ✓</p>
                    </div>
                  </div>
                  
                  <div class="info-card">
                    <h5>System Access</h5>
                    <div class="info-item">
                      <label>PERMISSIONS</label>
                      <p>Student Management, System Settings, Reports</p>
                    </div>
                    <div class="info-item">
                      <label>LAST LOGIN</label>
                      <p>Today, 09:15 AM</p>
                    </div>
                    <div class="info-item">
                      <label>ACCOUNT CREATED</label>
                      <p>January 15, 2024</p>
                    </div>
                    <div class="info-item">
                      <label>ACCOUNT STATUS</label>
                      <p class="text-success">Active ✓</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Sidebar Column -->
          <div class="col-lg-4 profile-sidebar">
            
            <!-- Quick Actions -->
            <div class="sidebar-section">
              <h4>Quick Actions</h4>
              <div class="quick-actions-list">
                <a href="admin.php" class="quick-action-btn text-decoration-none">
                  <i class="bi bi-speedometer2"></i>
                  <span>Dashboard</span>
                </a>
                
                <button class="quick-action-btn" id="printProfileBtn">
                  <i class="bi bi-printer"></i>
                  <span>Print Profile</span>
                </button>
                
                <button class="quick-action-btn" id="exportDataBtn">
                  <i class="bi bi-download"></i>
                  <span>Export Data</span>
                </button>
              </div>
            </div>
            
            <!-- System Status -->
            <div class="sidebar-section">
              <h4>System Status</h4>
              <div class="system-status">
                <div class="status-item">
                  <span class="status-label">Database</span>
                  <span class="status-value online">Online</span>
                </div>
                <div class="status-item">
                  <span class="status-label">Storage</span>
                  <span class="status-value">65% Used</span>
                </div>
                <div class="status-item">
                  <span class="status-label">Last Backup</span>
                  <span class="status-value">Today, 02:00 AM</span>
                </div>
                <div class="status-item">
                  <span class="status-label">Active Sessions</span>
                  <span class="status-value">3</span>
                </div>
              </div>
            </div>
            
            <!-- Important Contacts -->
            <div class="sidebar-section">
              <h4>Support Contacts</h4>
              <div class="contact-list">
                <div class="contact-item">
                  <div class="contact-icon">
                    <i class="bi bi-headset"></i>
                  </div>
                  <div class="contact-info">
                    <h6>IT Support</h6>
                    <small>(052) 742-1234</small>
                    <small>support@bicol-u.edu.ph</small>
                  </div>
                </div>
                
                <div class="contact-item">
                  <div class="contact-icon">
                    <i class="bi bi-shield-check"></i>
                  </div>
                  <div class="contact-info">
                    <h6>Security</h6>
                    <small>(052) 742-5678</small>
                    <small>security@bicol-u.edu.ph</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer id="footer" class="footer">
    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Bicol University</strong> <span>All Rights Reserved</span></p>
      <div class="credits">
        Student Management System v2.0 | Admin Portal
      </div>
    </div>
  </footer>

  <!-- Avatar Upload Modal -->
  <div class="modal fade" id="avatarUploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Change Profile Picture</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" enctype="multipart/form-data" id="avatarUploadForm">
          <div class="modal-body">
            <div class="avatar-preview text-center mb-4">
              <div class="avatar-preview-container">
                <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Preview" id="avatarPreview" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">
              </div>
              <small class="text-muted">Preview of your profile picture</small>
            </div>
            
            <div class="upload-options">
              <div class="upload-option">
                <input type="file" name="profile_image" id="avatarFileInput" accept="image/jpeg,image/jpg,image/png,image/gif" required>
                <label for="avatarFileInput" class="btn btn-outline-primary w-100">
                  <i class="bi bi-upload"></i> Choose Photo
                </label>
                <small class="text-muted d-block mt-2">Accepted formats: JPG, PNG, GIF (Max 5MB)</small>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="saveAvatarBtn">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <script>
    // Preview image before upload
    const avatarFileInput = document.getElementById('avatarFileInput');
    const avatarPreview = document.getElementById('avatarPreview');
    
    if (avatarFileInput) {
      avatarFileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          // Validate file size (max 5MB)
          if (file.size > 5 * 1024 * 1024) {
            alert('File size should be less than 5MB');
            this.value = '';
            return;
          }
          
          // Validate file type
          const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
          if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid image file (JPG, PNG, or GIF)');
            this.value = '';
            return;
          }
          
          const reader = new FileReader();
          reader.onload = function(e) {
            avatarPreview.src = e.target.result;
          };
          reader.readAsDataURL(file);
        }
      });
    }
    
    // Open modal when clicking change avatar button
    const changeAvatarBtn = document.getElementById('changeAvatarBtn');
    const avatarUploadModal = new bootstrap.Modal(document.getElementById('avatarUploadModal'));
    
    if (changeAvatarBtn) {
      changeAvatarBtn.addEventListener('click', function() {
        avatarUploadModal.show();
      });
    }
    
    // Profile dropdown functionality
    const profileTrigger = document.getElementById('profileTrigger');
    const profileDropdown = document.getElementById('profileDropdown');
    
    if (profileTrigger && profileDropdown) {
      profileTrigger.addEventListener('click', function(e) {
        e.stopPropagation();
        profileDropdown.classList.toggle('show');
      });
      
      // Close dropdown when clicking outside
      document.addEventListener('click', function(e) {
        if (!profileTrigger.contains(e.target) && !profileDropdown.contains(e.target)) {
          profileDropdown.classList.remove('show');
        }
      });
    }
  </script>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  
  <!-- Student Profile JS (reused for admin) -->
  <script src="assets/js/student-profile.js"></script>
  
  <!-- Dark Mode JS -->
  <script src="assets/js/dark-mode.js"></script>
  
  <!-- Notifications JS -->
  <script src="assets/js/notifications.js"></script>
</body>
</html>

