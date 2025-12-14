<?php
// Basic session + DB setup
session_start();
require_once('db.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: login.php');
    exit();
}

$studentId = (int)$_SESSION['user_id'];

// Initialize $fullName early to avoid undefined variable warning
$fullName = 'Student';

// Handle profile image upload
$uploadMessage = '';
$uploadSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    // Check if profile_image column exists, create if it doesn't
    $checkColumnSql = "SHOW COLUMNS FROM students LIKE 'profile_image'";
    $checkResult = $conn->query($checkColumnSql);
    $columnExists = ($checkResult && $checkResult->num_rows > 0);
    
    // Create column if it doesn't exist
    if (!$columnExists) {
        $createColumnSql = "ALTER TABLE students ADD COLUMN profile_image LONGTEXT";
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
            $updateSql = "UPDATE students SET profile_image = ? WHERE student_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            if ($updateStmt) {
                $updateStmt->bind_param('si', $imageBase64, $studentId);
                if ($updateStmt->execute()) {
                    $uploadSuccess = true;
                    $uploadMessage = 'Profile picture updated successfully!';
                    // Redirect to avoid resubmission on refresh
                    header('Location: profile.php?upload=success');
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

// Load current student info
$student = [
    'firstname' => '',
    'lastname' => '',
    'username' => '',
    'email' => '',
    'course' => '',
    'year_level' => '',
    'contact' => '',
    'address' => '',
    'profile_image' => ''
];

// Check if profile_image column exists
$checkColumnSql = "SHOW COLUMNS FROM students LIKE 'profile_image'";
$columnExists = false;
$checkResult = $conn->query($checkColumnSql);
if ($checkResult && $checkResult->num_rows > 0) {
    $columnExists = true;
}

// Build SQL query - conditionally include profile_image if column exists
if ($columnExists) {
    $sql = "SELECT s.firstname, s.lastname, s.course, s.year_level, s.email, s.contact, s.address,
                   s.date_of_birth, s.gender, s.nationality, s.marital_status, s.blood_type,
                   s.emergency_contact_name, s.emergency_contact_relationship, s.emergency_contact_phone,
                   s.profile_image,
                   sl.username, sl.student_email
            FROM students s
            JOIN student_login sl ON sl.student_id = s.student_id
            WHERE s.student_id = ?";
} else {
    $sql = "SELECT s.firstname, s.lastname, s.course, s.year_level, s.email, s.contact, s.address,
                   s.date_of_birth, s.gender, s.nationality, s.marital_status, s.blood_type,
                   s.emergency_contact_name, s.emergency_contact_relationship, s.emergency_contact_phone,
                   sl.username, sl.student_email
            FROM students s
            JOIN student_login sl ON sl.student_id = s.student_id
            WHERE s.student_id = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $studentId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $student['firstname'] = $row['firstname'];
    $student['lastname'] = $row['lastname'];
    $student['username'] = $row['username'];
    $student['email'] = $row['student_email'];
    $student['course'] = $row['course'];
    $student['year_level'] = $row['year_level'];
    $student['contact'] = $row['contact'] ?? '';
    $student['address'] = $row['address'] ?? '';
    $student['date_of_birth'] = $row['date_of_birth'] ?? '';
    $student['gender'] = $row['gender'] ?? '';
    $student['nationality'] = $row['nationality'] ?? '';
    $student['marital_status'] = $row['marital_status'] ?? '';
    $student['blood_type'] = $row['blood_type'] ?? '';
    $student['emergency_contact_name'] = $row['emergency_contact_name'] ?? '';
    $student['emergency_contact_relationship'] = $row['emergency_contact_relationship'] ?? '';
    $student['emergency_contact_phone'] = $row['emergency_contact_phone'] ?? '';
    $student['profile_image'] = ($columnExists && isset($row['profile_image'])) ? $row['profile_image'] : '';
    
    // Set fullName after loading data
    $fullName = htmlspecialchars(trim($student['firstname'] . ' ' . $student['lastname']));
    if (empty(trim($fullName))) {
        $fullName = 'Student';
    }
}
$stmt->close();

// Helper function to get profile image src
function getProfileImageSrc($profileImage) {
    if (!empty($profileImage)) {
        return 'data:image/jpeg;base64,' . $profileImage;
    }
    return 'assets/img/student-avatar.jpg';
}
$profileImageSrc = getProfileImageSrc($student['profile_image']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Student Profile | Bicol University</title>
  <meta name="description" content="Student Profile Dashboard">
  <meta name="keywords" content="student, profile, dashboard, university">

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
  
  <!-- Student Profile CSS -->
  <link href="assets/css/student-profile.css" rel="stylesheet">
  
  <!-- Dark Mode CSS -->
  <link href="assets/css/dark-mode.css" rel="stylesheet">
</head>

<body class="student-profile-page">

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
          <li><a href="index.php">Home</a></li>
          <li><a href="profile.php" class="active">Profile</a></li>
          <li><a href="course.php">Courses</a></li>
          <li><a href="grade.php">Grades</a></li>
          <li><a href="schedule.php">Schedule</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <!-- Profile Dropdown -->
      <div class="profile-dropdown">
        <div class="profile-trigger" id="profileTrigger">
          <div class="profile-avatar">
            <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Student Avatar" id="profileAvatar">
          </div>
          <div class="profile-info">
            <span class="profile-name" id="profileName"><?php echo $fullName ?: 'Student'; ?></span>
            <span class="profile-role">Student</span>
          </div>
          <i class="bi bi-chevron-down"></i>
        </div>
        
        <div class="profile-dropdown-menu" id="profileDropdown">
          <div class="dropdown-header">
            <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Student Avatar" id="dropdownAvatar">
            <div>
              <h6 id="dropdownName"><?php echo $fullName ?: 'Student'; ?></h6>
              <span><?php echo htmlspecialchars($student['course']); ?></span>
            </div>
          </div>
          <div class="dropdown-divider"></div>
          <a href="profile.php" class="dropdown-item">
            <i class="bi bi-person"></i> My Profile
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
        <?php $fullName = htmlspecialchars(trim($student['firstname'] . ' ' . $student['lastname'])); ?>

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

        <!-- Profile Completion Notice -->
        <?php if (!$student['date_of_birth'] && !$student['gender'] && !$student['emergency_contact_name']): ?>
        <div class="alert alert-info mb-4 d-flex align-items-center justify-content-between">
          <div>
            <i class="bi bi-info-circle"></i> <strong>Complete Your Profile</strong>
            <p class="mb-0 mt-1">Please complete your profile information to access all features.</p>
          </div>
          <a href="setprofile.php" class="btn btn-primary">
            <i class="bi bi-person-check"></i> Complete Profile
          </a>
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
              <h1 class="student-name" id="mainProfileName"><?php echo $fullName ?: 'Student'; ?></h1>
              <p class="student-id">Student ID: <?php echo htmlspecialchars($student['username']); ?></p>
              <div class="program-badges">
                <span class="program-badge"><?php echo htmlspecialchars($student['course']); ?></span>
                <span class="year-badge"><?php echo htmlspecialchars($student['year_level']); ?></span>
              </div>
            </div>
          </div>
          
          <!-- Progress Section -->
          <div class="progress-section">
            <h4>Program Progress</h4>
            <div class="progress-bar-container">
              <div class="progress-bar" style="width: 65%;"></div>
            </div>
            <div class="progress-text">
              <span>65% Complete</span>
              <span>78 of 120 credits</span>
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
                  <button class="nav-link" id="academic-tab" data-bs-toggle="tab" data-bs-target="#academic" type="button">
                    <i class="bi bi-journal-bookmark"></i> Academic
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact-info" type="button">
                    <i class="bi bi-telephone"></i> Contact
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button">
                    <i class="bi bi-folder"></i> Documents
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
                      <p id="infoFullName"><?php echo $fullName ?: 'Student'; ?></p>
                    </div>
                    <div class="info-item">
                      <label>DATE OF BIRTH</label>
                      <p id="infoDob"><?php echo $student['date_of_birth'] ? date('F d, Y', strtotime($student['date_of_birth'])) : '—'; ?></p>
                    </div>
                    <div class="info-item">
                      <label>GENDER</label>
                      <p id="infoGender"><?php echo htmlspecialchars($student['gender'] ?: '—'); ?></p>
                    </div>
                    <div class="info-item">
                      <label>ADDRESS</label>
                      <p id="infoAddress"><?php echo htmlspecialchars($student['address'] ?: '—'); ?></p>
                    </div>
                  </div>
                  
                  <div class="info-card">
                    <h5>Additional Details</h5>
                    <div class="info-item">
                      <label>NATIONALITY</label>
                      <p id="infoNationality"><?php echo htmlspecialchars($student['nationality'] ?: '—'); ?></p>
                    </div>
                    <div class="info-item">
                      <label>MARITAL STATUS</label>
                      <p id="infoMaritalStatus"><?php echo htmlspecialchars($student['marital_status'] ?: '—'); ?></p>
                    </div>
                    <div class="info-item">
                      <label>BLOOD TYPE</label>
                      <p id="infoBloodType"><?php echo htmlspecialchars($student['blood_type'] ?: '—'); ?></p>
                    </div>
                    <div class="info-item">
                      <label>EMERGENCY CONTACT</label>
                      <p id="infoEmergencyContact">
                        <?php if ($student['emergency_contact_name']): ?>
                          <?php echo htmlspecialchars($student['emergency_contact_name']); ?>
                          <?php if ($student['emergency_contact_relationship']): ?>
                            <br><small class="text-muted">(<?php echo htmlspecialchars($student['emergency_contact_relationship']); ?>)</small>
                          <?php endif; ?>
                          <?php if ($student['emergency_contact_phone']): ?>
                            <br><small class="text-muted"><?php echo htmlspecialchars($student['emergency_contact_phone']); ?></small>
                          <?php endif; ?>
                        <?php else: ?>
                          —
                        <?php endif; ?>
                      </p>
                    </div>
                    <a href="setprofile.php" class="btn btn-primary btn-sm mt-3">
                      <i class="bi bi-pencil"></i> Edit Information
                    </a>
                  </div>
                </div>
                
                <!-- Recent Activities -->
                <div class="recent-activities">
                  <h5>Recent Activities</h5>
                  <div class="activity-list">
                    <div class="activity-item">
                      <div class="activity-icon blue">
                        <i class="bi bi-check-circle"></i>
                      </div>
                      <div class="activity-content">
                        <h6>Enrolled in "Data Structures" course</h6>
                        <small>Today, 10:30 AM</small>
                      </div>
                    </div>
                    
                    <div class="activity-item">
                      <div class="activity-icon green">
                        <i class="bi bi-arrow-up-circle"></i>
                      </div>
                      <div class="activity-content">
                        <h6>Submitted assignment for Algorithms</h6>
                        <small>Yesterday, 3:45 PM</small>
                      </div>
                    </div>
                    
                    <div class="activity-item">
                      <div class="activity-icon orange">
                        <i class="bi bi-calendar-check"></i>
                      </div>
                      <div class="activity-content">
                        <h6>Upcoming exam: Database Systems</h6>
                        <small>March 25, 2024 • 2 days from now</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Academic Information Tab -->
              <div class="tab-pane fade" id="academic" role="tabpanel">
                <div class="info-grid">
                  <div class="info-card">
                    <h5>Academic Information</h5>
                    <div class="info-item">
                      <label>STUDENT ID</label>
                      <p><?php echo htmlspecialchars($student['username']); ?></p>
                    </div>
                    <div class="info-item">
                      <label>PROGRAM</label>
                      <p><?php echo htmlspecialchars($student['course']); ?></p>
                    </div>
                    <div class="info-item">
                      <label>YEAR LEVEL</label>
                      <p><?php echo htmlspecialchars($student['year_level']); ?></p>
                    </div>
                    <div class="info-item">
                      <label>COLLEGE</label>
                      <p>College of Science</p>
                    </div>
                  </div>
                  
                  <div class="info-card">
                    <h5>Academic Status</h5>
                    <div class="info-item">
                      <label>CURRENT GPA</label>
                      <p>3.85 / 4.0</p>
                    </div>
                    <div class="info-item">
                      <label>TOTAL CREDITS</label>
                      <p>78 credits completed</p>
                    </div>
                    <div class="info-item">
                      <label>ACADEMIC STANDING</label>
                      <p class="text-success">Good Standing ✓</p>
                    </div>
                    <div class="info-item">
                      <label>SCHOLARSHIP</label>
                      <p>Academic Excellence Scholarship</p>
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
                      <p id="infoEmail"><?php echo htmlspecialchars($student['email']); ?></p>
                    </div>
                    <div class="info-item">
                      <label>PHONE NUMBER</label>
                      <p id="infoPhone"><?php echo htmlspecialchars($student['contact'] ?: '—'); ?></p>
                    </div>
                  </div>
                  
                  <div class="info-card">
                    <h5>Emergency Contact</h5>
                    <div class="info-item">
                      <label>CONTACT PERSON</label>
                      <p id="infoEmergencyContact"><?php echo htmlspecialchars($student['emergency_contact_name'] ?: '—'); ?></p>
                    </div>
                    <div class="info-item">
                      <label>RELATIONSHIP</label>
                      <p><?php echo htmlspecialchars($student['emergency_contact_relationship'] ?: '—'); ?></p>
                    </div>
                    <div class="info-item">
                      <label>PHONE NUMBER</label>
                      <p id="infoEmergencyPhone"><?php echo htmlspecialchars($student['emergency_contact_phone'] ?: '—'); ?></p>
                    </div>
                    <a href="setprofile.php" class="btn btn-primary btn-sm mt-3">
                      <i class="bi bi-pencil"></i> Edit Contact
                    </a>
                  </div>
                </div>
              </div>
              
              <!-- Documents Tab -->
              <div class="tab-pane fade" id="documents" role="tabpanel">
                <div class="document-list">
                  <div class="document-item">
                    <div class="document-icon pdf">
                      <i class="bi bi-file-earmark-pdf"></i>
                    </div>
                    <div class="document-info">
                      <h6>Transcript of Records</h6>
                      <small>Uploaded: Jan 15, 2024 • Size: 2.4 MB</small>
                    </div>
                    <div class="document-actions">
                      <button class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-download"></i>
                      </button>
                    </div>
                  </div>
                  
                  <div class="document-item">
                    <div class="document-icon image">
                      <i class="bi bi-file-earmark-image"></i>
                    </div>
                    <div class="document-info">
                      <h6>ID Picture</h6>
                      <small>Uploaded: Dec 10, 2023 • Size: 1.2 MB</small>
                    </div>
                    <div class="document-actions">
                      <button class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-download"></i>
                      </button>
                    </div>
                  </div>
                  
                  <div class="document-item">
                    <div class="document-icon word">
                      <i class="bi bi-file-earmark-word"></i>
                    </div>
                    <div class="document-info">
                      <h6>Application Form</h6>
                      <small>Uploaded: Aug 5, 2023 • Size: 850 KB</small>
                    </div>
                    <div class="document-actions">
                      <button class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-download"></i>
                      </button>
                    </div>
                  </div>
                  
                  <button class="btn btn-primary btn-sm mt-3">
                    <i class="bi bi-cloud-upload"></i> Upload Document
                  </button>
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
                <a href="course.php" class="quick-action-btn text-decoration-none">
                  <i class="bi bi-journal-text"></i>
                  <span>View Courses</span>
                </a>
                
                <a href="grade.php" class="quick-action-btn text-decoration-none">
                  <i class="bi bi-graph-up"></i>
                  <span>Check Grades</span>
                </a>
                
                <a href="schedule.php" class="quick-action-btn text-decoration-none">
                  <i class="bi bi-calendar-week"></i>
                  <span>View Schedule</span>
                </a>
                
                <button class="quick-action-btn" id="printProfileBtn">
                  <i class="bi bi-printer"></i>
                  <span>Print Profile</span>
                </button>
              </div>
            </div>
            
            <!-- Upcoming Events -->
            <div class="sidebar-section">
              <h4>Upcoming Events</h4>
              <div class="event-list">
                <div class="event-item">
                  <div class="event-date">
                    <span class="event-day">25</span>
                    <span class="event-month">MAR</span>
                  </div>
                  <div class="event-details">
                    <h6>Midterm Exams Begin</h6>
                    <small>All Departments</small>
                  </div>
                </div>
                
                <div class="event-item">
                  <div class="event-date">
                    <span class="event-day">05</span>
                    <span class="event-month">APR</span>
                  </div>
                  <div class="event-details">
                    <h6>University Foundation Day</h6>
                    <small>No Classes</small>
                  </div>
                </div>
                
                <div class="event-item">
                  <div class="event-date">
                    <span class="event-day">15</span>
                    <span class="event-month">APR</span>
                  </div>
                  <div class="event-details">
                    <h6>Final Submission Deadline</h6>
                    <small>Research Papers</small>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Important Contacts -->
            <div class="sidebar-section">
              <h4>Important Contacts</h4>
              <div class="contact-list">
                <div class="contact-item">
                  <div class="contact-icon">
                    <i class="bi bi-person-badge"></i>
                  </div>
                  <div class="contact-info">
                    <h6>Academic Advisor</h6>
                    <small>Dr. Maria Santos</small>
                    <small>maria.santos@bicol-u.edu.ph</small>
                  </div>
                </div>
                
                <div class="contact-item">
                  <div class="contact-icon">
                    <i class="bi bi-headset"></i>
                  </div>
                  <div class="contact-info">
                    <h6>Student Support</h6>
                    <small>(052) 742-1234</small>
                    <small>support@bicol-u.edu.ph</small>
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
        Student Management System v2.0
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
    
    // Form submission - let it submit naturally, the modal will close automatically
    const avatarUploadForm = document.getElementById('avatarUploadForm');
    if (avatarUploadForm) {
      avatarUploadForm.addEventListener('submit', function(e) {
        // Form will submit normally, PHP will handle it
        // Bootstrap modal will close on form submit if successful
      });
    }
  </script>

  <!-- Edit Info Modal -->
  <div class="modal fade" id="editInfoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalTitle">Edit Information</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="editInfoForm">
            <div class="mb-3">
              <label class="form-label">Full Name</label>
              <input type="text" class="form-control" id="editFullName">
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Date of Birth</label>
                  <input type="date" class="form-control" id="editDob">
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Gender</label>
                  <select class="form-select" id="editGender">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Address</label>
              <textarea class="form-control" id="editAddress" rows="2"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveInfoBtn">Save Changes</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  
  <!-- Student Profile JS -->
  <script src="assets/js/student-profile.js"></script>
  
  <!-- Dark Mode JS -->
  <script src="assets/js/dark-mode.js"></script>
</body>
</html>