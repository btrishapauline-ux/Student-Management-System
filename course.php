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

// Initialize variables
$fullName = 'Student';
$student = [
    'firstname' => '',
    'lastname' => '',
    'username' => '',
    'email' => '',
    'course' => '',
    'year_level' => '',
    'profile_image' => ''
];

// Check if profile_image column exists
$checkColumnSql = "SHOW COLUMNS FROM students LIKE 'profile_image'";
$columnExists = false;
$checkResult = $conn->query($checkColumnSql);
if ($checkResult && $checkResult->num_rows > 0) {
    $columnExists = true;
}

// Build SQL query
if ($columnExists) {
    $sql = "SELECT s.firstname, s.lastname, s.course, s.year_level, s.email,
                   s.profile_image,
                   sl.username
            FROM students s
            JOIN student_login sl ON sl.student_id = s.student_id
            WHERE s.student_id = ?";
} else {
    $sql = "SELECT s.firstname, s.lastname, s.course, s.year_level, s.email,
                   sl.username
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
    $student['email'] = $row['email'];
    $student['course'] = $row['course'];
    $student['year_level'] = $row['year_level'];
    $student['profile_image'] = ($columnExists && isset($row['profile_image'])) ? $row['profile_image'] : '';
    
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

// Sample courses data (replace with database query in production)
$courses = [
    ['code' => 'CS 201', 'name' => 'Data Structures', 'instructor' => 'Dr. Maria Santos', 'schedule' => 'Mon, Wed, Fri 8:00 AM - 9:30 AM', 'credits' => 3, 'room' => 'Room 101'],
    ['code' => 'CS 202', 'name' => 'Algorithms', 'instructor' => 'Prof. Juan Dela Cruz', 'schedule' => 'Tue, Thu 10:00 AM - 11:30 AM', 'credits' => 3, 'room' => 'Room 205'],
    ['code' => 'CS 203', 'name' => 'Database Systems', 'instructor' => 'Dr. Ana Garcia', 'schedule' => 'Mon, Wed 2:00 PM - 3:30 PM', 'credits' => 3, 'room' => 'Lab 301'],
    ['code' => 'MATH 301', 'name' => 'Discrete Mathematics', 'instructor' => 'Prof. Roberto Lim', 'schedule' => 'Tue, Thu 1:00 PM - 2:30 PM', 'credits' => 3, 'room' => 'Room 150'],
    ['code' => 'ENGL 201', 'name' => 'Technical Writing', 'instructor' => 'Prof. Linda Reyes', 'schedule' => 'Fri 9:00 AM - 12:00 PM', 'credits' => 2, 'room' => 'Room 302'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>My Courses | Bicol University</title>
  <meta name="description" content="Student Courses Dashboard">
  <meta name="keywords" content="student, courses, dashboard, university">

  <!-- Favicons -->
  <link href="assets/img/logo.png" rel="icon" type="image/png">
  <link href="assets/img/logo.png" rel="apple-touch-icon">

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
          <li><a href="profile.php">Profile</a></li>
          <li><a href="course.php" class="active">Courses</a></li>
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
    <section class="profile-content section">
      <div class="container">
        
        <!-- Profile Header Card -->
        <div class="profile-header-card">
          <div class="profile-avatar-section">
            <div class="profile-avatar-container">
              <div class="avatar-wrapper">
                <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="<?php echo htmlspecialchars($fullName); ?>" class="profile-main-avatar">
              </div>
            </div>
            
            <div class="profile-info-main">
              <h1 class="student-name"><?php echo $fullName ?: 'Student'; ?></h1>
              <p class="student-id">Student ID: <?php echo htmlspecialchars($student['username']); ?></p>
              <div class="program-badges">
                <span class="program-badge"><?php echo htmlspecialchars($student['course']); ?></span>
                <span class="year-badge"><?php echo htmlspecialchars($student['year_level']); ?></span>
              </div>
            </div>
          </div>
          
          <div class="progress-section">
            <h4>Current Semester</h4>
            <p style="margin: 0; font-size: 18px; color: #37517e; font-weight: 600;">First Semester 2024</p>
            <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;"><?php echo count($courses); ?> Courses Enrolled</p>
          </div>
        </div>

        <div class="row">
          <!-- Main Content Column -->
          <div class="col-lg-12">
            
            <!-- Page Title -->
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h2 class="mb-0">My Courses</h2>
              <button class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Course
              </button>
            </div>

            <!-- Courses Grid -->
            <div class="row">
              <?php foreach ($courses as $course): ?>
              <div class="col-md-6 col-lg-4 mb-4">
                <div class="info-card h-100">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                      <h5 class="mb-1" style="color: #37517e;"><?php echo htmlspecialchars($course['code']); ?></h5>
                      <h6 class="mb-0" style="color: #47b2e4;"><?php echo htmlspecialchars($course['name']); ?></h6>
                    </div>
                    <span class="badge bg-primary"><?php echo $course['credits']; ?> Credits</span>
                  </div>
                  
                  <div class="info-item mb-2">
                    <label style="font-size: 12px; color: #999;">INSTRUCTOR</label>
                    <p style="margin: 0; font-size: 14px;"><?php echo htmlspecialchars($course['instructor']); ?></p>
                  </div>
                  
                  <div class="info-item mb-2">
                    <label style="font-size: 12px; color: #999;">SCHEDULE</label>
                    <p style="margin: 0; font-size: 14px;">
                      <i class="bi bi-calendar-week"></i> <?php echo htmlspecialchars($course['schedule']); ?>
                    </p>
                  </div>
                  
                  <div class="info-item mb-3">
                    <label style="font-size: 12px; color: #999;">ROOM</label>
                    <p style="margin: 0; font-size: 14px;">
                      <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($course['room']); ?>
                    </p>
                  </div>
                  
                  <div class="d-flex gap-2">
                    <a href="grade.php" class="btn btn-outline-primary btn-sm flex-fill">
                      <i class="bi bi-graph-up"></i> View Grades
                    </a>
                    <a href="schedule.php" class="btn btn-outline-secondary btn-sm flex-fill">
                      <i class="bi bi-calendar"></i> Schedule
                    </a>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
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

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  
  <!-- Student Profile JS -->
  <script src="assets/js/student-profile.js"></script>
  
  <!-- Dark Mode JS -->
  <script src="assets/js/dark-mode.js"></script>
</body>
</html>
