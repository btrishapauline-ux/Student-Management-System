<?php
// Display errors for debugging (REMOVE THIS LINE ON A LIVE SERVER when done)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Require session to ensure only admin can access
require_once('session.php');
require_once('db.php'); 

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("location: admin_login.php");
    exit();
}

// Initialize count variable
$totalStudents = 0;

// Check if the connection worked
if (isset($conn) && $conn->connect_error) {
    // If connection failed, assign the error message
    $totalStudents = "CONN ERROR: " . $conn->connect_error; 
} else if (isset($conn)) {
    // 2. Prepare and Execute the SQL COUNT statement
    // Counts rows in the 'students' table
    $sql_count = "SELECT COUNT(student_id) AS total_students FROM students";
    $result = $conn->query($sql_count);
    
    // 3. Check for SQL query failure
    if ($result === false) {
        $totalStudents = "DB ERROR: " . $conn->error;
    } else {
        // 4. Fetch the result successfully
        $row = $result->fetch_assoc();
        // $totalStudents will now hold the number (e.g., 10)
        $totalStudents = $row['total_students'];
    }
} else {
    // Fallback if $conn is not set, likely due to error in db.php
    $totalStudents = "FATAL ERROR: Database connection object not found.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Admin Dashboard | Student Management</title>
    <meta name="description" content="Student Management Admin Dashboard">
    <meta name="keywords" content="admin, student, management, dashboard">

    <link href="assets/img/logo.png" rel="icon" type="image/png">
    <link href="assets/img/logo.png" rel="apple-touch-icon">

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Jost:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

    <link href="assets/css/main.css" rel="stylesheet">
    
    <link href="assets/css/student-profile.css" rel="stylesheet">
    
    <link href="assets/css/admin.css" rel="stylesheet">
    
    <!-- Dark Mode CSS -->
    <link href="assets/css/dark-mode.css" rel="stylesheet">
</head>

<body class="admin-page">

    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">

            <a href="index.php" class="logo d-flex align-items-center me-auto">
                <img src="assets/img/logo.png" alt="Bicol University Logo" style="height: 40px; margin-right: 10px;">
                <h1 class="sitename">BICOL UNIVERSITY</h1>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>

                   
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

            <div class="profile-dropdown">
                <div class="profile-trigger" id="profileTrigger">
                    <div class="profile-avatar">
                        <img src="assets/img/admin-avatar.jpg" alt="Admin Avatar" id="profileAvatar">
                    </div>
                    <div class="profile-info">
                        <span class="profile-name" id="profileName">Admin User</span>
                        <span class="profile-role">Administrator</span>
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </div>
                
                <div class="profile-dropdown-menu" id="profileDropdown">
                    <div class="dropdown-header">
                        <img src="assets/img/admin-avatar.jpg" alt="Admin Avatar" id="dropdownAvatar">
                        <div>
                            <h6 id="dropdownName">Admin User</h6>
                            <span>System Administrator</span>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="admin.php" class="dropdown-item">
                        <i class="bi bi-speedometer2"></i> Admin Dashboard
                    </a>
                    <a href="admin_profile.php" class="dropdown-item">
                        <i class="bi bi-speedometer2"></i> Admin Profile
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

    <footer id="footer" class="footer">
        <div class="container copyright text-center mt-4">
            <p>© <span>Copyright</span> <strong class="px-1 sitename">Bicol University</strong> <span>All Rights Reserved</span></p>
            <div class="credits">
                Student Management System v2.0 | Admin Dashboard
            </div>
        </div>
    </footer>

    <div class="modal fade" id="addStudentModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addStudentForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="firstName" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="lastName" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Student ID *</label>
                                    <input type="text" class="form-control" id="studentId" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Program *</label>
                                    <select class="form-select" id="program" required>
                                        <option value="">Select Program</option>
                                        <option value="BS Computer Science">BS Computer Science</option>
                                        <option value="BS Information Technology">BS Information Technology</option>
                                        <option value="BS Information Systems">BS Information Systems</option>
                                        <option value="BS Computer Engineering">BS Computer Engineering</option>
                                        <option value="BS Electronics Engineering">BS Electronics Engineering</option>
                                        <option value="BS Electrical Engineering">BS Electrical Engineering</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Year Level *</label>
                                    <select class="form-select" id="yearLevel" required>
                                        <option value="">Select Year</option>
                                        <option value="1st Year">1st Year</option>
                                        <option value="2nd Year">2nd Year</option>
                                        <option value="3rd Year">3rd Year</option>
                                        <option value="4th Year">4th Year</option>
                                        <option value="5th Year">5th Year</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="dateOfBirth">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" id="gender">
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" id="address" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phoneNumber">
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isActive" checked>
                                <label class="form-check-label" for="isActive">
                                    Active Student
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveStudentBtn">Save Student</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editStudentModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Student Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editStudentForm" enctype="multipart/form-data">
                        <input type="hidden" id="editStudentIdHidden">
                        <input type="hidden" id="editProfileImageBase64">
                        
                        <h6 class="mb-3 text-primary"><i class="bi bi-person"></i> Basic Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Student ID *</label>
                                    <input type="text" class="form-control" id="editStudentId" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Username *</label>
                                    <input type="text" class="form-control" id="editUsername" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="editFirstName" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="editLastName" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="editEmail" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="editPhoneNumber">
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="mb-3 mt-4 text-primary"><i class="bi bi-graduation-cap"></i> Academic Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Program/Course *</label>
                                    <select class="form-select" id="editProgram" required>
                                        <option value="">Select Program</option>
                                        <option value="BS Information Technology">BS Information Technology</option>
                                        <option value="BS Computer Science">BS Computer Science</option>
                                        <option value="BS Electrical Engineering">BS Electrical Engineering</option>
                                        <option value="BS Mechanical Engineering">BS Mechanical Engineering</option>
                                        <option value="BS Education">BS Education</option>
                                        <option value="BS Nursing">BS Nursing</option>
                                        <option value="BS Business Administration">BS Business Administration</option>
                                        <option value="BS Accountancy">BS Accountancy</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Year Level *</label>
                                    <select class="form-select" id="editYearLevel" required>
                                        <option value="">Select Year</option>
                                        <option value="1st Year">1st Year</option>
                                        <option value="2nd Year">2nd Year</option>
                                        <option value="3rd Year">3rd Year</option>
                                        <option value="4th Year">4th Year</option>
                                        <option value="5th Year">5th Year</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="mb-3 mt-4 text-primary"><i class="bi bi-person-vcard"></i> Personal Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="editDateOfBirth">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" id="editGender">
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nationality</label>
                                    <input type="text" class="form-control" id="editNationality" placeholder="Filipino">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Marital Status</label>
                                    <select class="form-select" id="editMaritalStatus">
                                        <option value="">Select Status</option>
                                        <option value="Single">Single</option>
                                        <option value="Married">Married</option>
                                        <option value="Divorced">Divorced</option>
                                        <option value="Widowed">Widowed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Blood Type</label>
                                    <select class="form-select" id="editBloodType">
                                        <option value="">Select Blood Type</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" id="editAddress" rows="2"></textarea>
                        </div>
                        
                        <h6 class="mb-3 mt-4 text-primary"><i class="bi bi-person-exclamation"></i> Emergency Contact</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Contact Person</label>
                                    <input type="text" class="form-control" id="editEmergencyContactName">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Relationship</label>
                                    <select class="form-select" id="editEmergencyContactRelationship">
                                        <option value="">Select Relationship</option>
                                        <option value="Father">Father</option>
                                        <option value="Mother">Mother</option>
                                        <option value="Guardian">Guardian</option>
                                        <option value="Spouse">Spouse</option>
                                        <option value="Sibling">Sibling</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="editEmergencyContactPhone">
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="mb-3 mt-4 text-primary"><i class="bi bi-image"></i> Profile Image</h6>
                        <div class="mb-3">
                            <div class="text-center mb-3">
                                <img id="editProfileImagePreview" src="assets/img/student-avatar.jpg" 
                                     alt="Profile Preview" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #dee2e6;">
                            </div>
                            <label class="form-label">Upload New Profile Image</label>
                            <input type="file" class="form-control" id="editProfileImage" accept="image/jpeg,image/jpg,image/png,image/gif">
                            <small class="text-muted">Accepted formats: JPG, PNG, GIF (Max 5MB)</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateStudentBtn">Update Student</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this student record? This action cannot be undone.</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Warning:</strong> Deleting this student will remove all associated records.
                    </div>
                    <div class="student-info-preview" id="deleteStudentInfo">
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Student</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewStudentModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Student Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="student-details-view" id="studentDetails">
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="editFromViewBtn">Edit Student</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    
    <script src="assets/js/admin.js"></script>
    
    <!-- Dark Mode JS -->
    <script src="assets/js/dark-mode.js"></script>
    
    <script>
        // Profile image preview in edit modal
        document.getElementById('editProfileImage')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('editProfileImagePreview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>