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

    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

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
</head>

<body class="admin-page">

    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">

            <a href="index.html" class="logo d-flex align-items-center me-auto">
                <h1 class="sitename">BICOL UNIVERSITY</h1>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="student-profile.html">Profile</a></li>
                    <li><a href="student-courses.html">Courses</a></li>
                    <li><a href="student-grades.html">Grades</a></li>
                    <li><a href="student-schedule.html">Schedule</a></li>
                    <li><a href="admin-page.html" class="active">Admin</a></li>
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
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>

        </div>
    </header>

    <main class="main">
        <section class="admin-content section">
            <div class="container">
                
                <div class="admin-header">
                    <h1>Student Management Dashboard</h1>
                    <p>Manage all student records, add new students, update information, and search through records</p>
                </div>

                <div class="admin-stats">
                    <div class="stat-card">
                        <div class="stat-icon total">
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <h3 id="totalStudents"><?php echo $totalStudents; ?></h3> 
                            <p>Total Students</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon active">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div>
                            <h3 id="activeStudents">0</h3>
                            <p>Active Students</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon new">
                            <i class="bi bi-person-plus"></i>
                        </div>
                        <div>
                            <h3 id="newStudents">0</h3>
                            <p>New This Month</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon pending">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div>
                            <h3 id="pendingStudents">0</h3>
                            <p>Pending Updates</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        
                        <div class="action-bar">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="search-box">
                                        <i class="bi bi-search"></i>
                                        <input type="text" id="searchInput" placeholder="Search students by name, ID, or email...">
                                        <button class="search-clear" id="clearSearch">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary w-100" id="addStudentBtn">
                                        <i class="bi bi-person-plus"></i> Add New Student
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="students-table-container">
                            <div class="table-responsive">
                                <table class="table" id="studentsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Program</th>
                                            <th>Year</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="studentsTableBody">
                                        </tbody>
                                </table>
                            </div>
                            
                            <div class="text-center py-5" id="loadingState">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3">Loading student data...</p>
                            </div>
                            
                            <div class="text-center py-5 d-none" id="emptyState">
                                <i class="bi bi-people display-1 text-muted"></i>
                                <h4 class="mt-3">No students found</h4>
                                <p class="text-muted">Add your first student using the "Add New Student" button</p>
                            </div>
                            
                            <nav aria-label="Student pagination" class="mt-4" id="paginationContainer">
                                <ul class="pagination justify-content-center" id="pagination">
                                    </ul>
                            </nav>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 admin-sidebar">
                        
                        <div class="sidebar-section">
                            <h4>Quick Actions</h4>
                            <div class="quick-actions-list">
                                <button class="quick-action-btn" id="exportDataBtn">
                                    <i class="bi bi-download"></i>
                                    <span>Export Data</span>
                                </button>
                                
                                <button class="quick-action-btn" id="bulkUploadBtn">
                                    <i class="bi bi-upload"></i>
                                    <span>Bulk Upload</span>
                                </button>
                                
                                <button class="quick-action-btn" id="manageProgramsBtn">
                                    <i class="bi bi-journal-bookmark"></i>
                                    <span>Manage Programs</span>
                                </button>
                                
                                <button class="quick-action-btn" id="reportsBtn">
                                    <i class="bi bi-graph-up"></i>
                                    <span>Generate Reports</span>
                                </button>
                                
                                <button class="quick-action-btn" id="refreshDataBtn">
                                    <i class="bi bi-arrow-clockwise"></i>
                                    <span>Refresh Data</span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="sidebar-section">
                            <h4>Recent Activity</h4>
                            <div class="activity-list" id="recentActivity">
                                <div class="activity-item">
                                    <div class="activity-icon blue">
                                        <i class="bi bi-person-plus"></i>
                                    </div>
                                    <div class="activity-content">
                                        <h6>New student added</h6>
                                        <small>Juan Dela Cruz - 2 minutes ago</small>
                                    </div>
                                </div>
                                
                                <div class="activity-item">
                                    <div class="activity-icon green">
                                        <i class="bi bi-pencil"></i>
                                    </div>
                                    <div class="activity-content">
                                        <h6>Student record updated</h6>
                                        <small>Maria Santos - 15 minutes ago</small>
                                    </div>
                                </div>
                                
                                <div class="activity-item">
                                    <div class="activity-icon orange">
                                        <i class="bi bi-trash"></i>
                                    </div>
                                    <div class="activity-content">
                                        <h6>Student record deleted</h6>
                                        <small>John Doe - 1 hour ago</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
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
                    <form id="editStudentForm">
                        <input type="hidden" id="editStudentId">
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
                                    <label class="form-label">Student ID *</label>
                                    <input type="text" class="form-control" id="editStudentId" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="editEmail" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Program *</label>
                                    <select class="form-select" id="editProgram" required>
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
                        
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" id="editAddress" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="editPhoneNumber">
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="editIsActive">
                                <label class="form-check-label" for="editIsActive">
                                    Active Student
                                </label>
                            </div>
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
</body>
</html>