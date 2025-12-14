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

// Sample grades data (replace with database query in production)
$grades = [
    [
        'code' => 'CS 201',
        'name' => 'Data Structures',
        'instructor' => 'Dr. Maria Santos',
        'credits' => 3,
        'midterm' => 88,
        'final' => 92,
        'average' => 90,
        'grade' => 'A',
        'remarks' => 'PASSED'
    ],
    [
        'code' => 'CS 202',
        'name' => 'Algorithms',
        'instructor' => 'Prof. Juan Dela Cruz',
        'credits' => 3,
        'midterm' => 85,
        'final' => 89,
        'average' => 87,
        'grade' => 'B+',
        'remarks' => 'PASSED'
    ],
    [
        'code' => 'CS 203',
        'name' => 'Database Systems',
        'instructor' => 'Dr. Ana Garcia',
        'credits' => 3,
        'midterm' => 90,
        'final' => 93,
        'average' => 91.5,
        'grade' => 'A',
        'remarks' => 'PASSED'
    ],
    [
        'code' => 'MATH 301',
        'name' => 'Discrete Mathematics',
        'instructor' => 'Prof. Roberto Lim',
        'credits' => 3,
        'midterm' => 82,
        'final' => 86,
        'average' => 84,
        'grade' => 'B',
        'remarks' => 'PASSED'
    ],
    [
        'code' => 'ENGL 201',
        'name' => 'Technical Writing',
        'instructor' => 'Prof. Linda Reyes',
        'credits' => 2,
        'midterm' => 91,
        'final' => 94,
        'average' => 92.5,
        'grade' => 'A',
        'remarks' => 'PASSED'
    ],
];

// Calculate overall GPA
$totalPoints = 0;
$totalCredits = 0;
$gradePoints = ['A' => 4.0, 'B+' => 3.5, 'B' => 3.0, 'C+' => 2.5, 'C' => 2.0, 'D' => 1.0, 'F' => 0.0];

foreach ($grades as $gradeData) {
    $points = isset($gradePoints[$gradeData['grade']]) ? $gradePoints[$gradeData['grade']] : 0;
    $totalPoints += $points * $gradeData['credits'];
    $totalCredits += $gradeData['credits'];
}

$gpa = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>My Grades | Bicol University</title>
  <meta name="description" content="Student Grades Dashboard">
  <meta name="keywords" content="student, grades, dashboard, university">

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
  
  <style>
    .grade-card {
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
      margin-bottom: 20px;
    }
    .grade-header {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: 15px;
      padding-bottom: 15px;
      border-bottom: 2px solid #e7f4ff;
    }
    .grade-badge {
      font-size: 32px;
      font-weight: 700;
      color: #47b2e4;
      padding: 10px 20px;
      background: #e7f4ff;
      border-radius: 8px;
    }
    .gpa-card {
      background: linear-gradient(135deg, #47b2e4 0%, #37517e 100%);
      color: white;
      border-radius: 10px;
      padding: 30px;
      text-align: center;
      box-shadow: 0 4px 20px rgba(71, 178, 228, 0.3);
    }
    .gpa-value {
      font-size: 48px;
      font-weight: 700;
      margin: 10px 0;
    }
    .grade-table {
      width: 100%;
    }
    .grade-table th {
      background: #f8f9fa;
      padding: 12px;
      text-align: left;
      font-weight: 600;
      color: #37517e;
      font-size: 14px;
    }
    .grade-table td {
      padding: 15px 12px;
      border-bottom: 1px solid #e7f4ff;
    }
    .grade-table tr:last-child td {
      border-bottom: none;
    }
    .grade-passed {
      color: #28a745;
      font-weight: 600;
    }
    .grade-failed {
      color: #dc3545;
      font-weight: 600;
    }
  </style>
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
          <li><a href="course.php">Courses</a></li>
          <li><a href="grade.php" class="active">Grades</a></li>
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
            <h4>GPA</h4>
            <div class="gpa-value"><?php echo number_format($gpa, 2); ?></div>
            <p style="margin: 0; color: #666; font-size: 14px;">Out of 4.0</p>
          </div>
        </div>

        <div class="row">
          <!-- Main Content Column -->
          <div class="col-lg-12">
            
            <!-- Page Title -->
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h2 class="mb-0">My Grades - First Semester 2024</h2>
              <button class="btn btn-primary">
                <i class="bi bi-download"></i> Download Transcript
              </button>
            </div>

            <!-- Grades Table Card -->
            <div class="grade-card">
              <table class="grade-table">
                <thead>
                  <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Instructor</th>
                    <th>Credits</th>
                    <th>Midterm</th>
                    <th>Final</th>
                    <th>Average</th>
                    <th>Grade</th>
                    <th>Remarks</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($grades as $gradeData): ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($gradeData['code']); ?></strong></td>
                    <td><?php echo htmlspecialchars($gradeData['name']); ?></td>
                    <td><?php echo htmlspecialchars($gradeData['instructor']); ?></td>
                    <td><?php echo $gradeData['credits']; ?></td>
                    <td><?php echo $gradeData['midterm']; ?></td>
                    <td><?php echo $gradeData['final']; ?></td>
                    <td><strong><?php echo number_format($gradeData['average'], 1); ?></strong></td>
                    <td>
                      <span class="grade-badge" style="font-size: 18px; padding: 5px 15px;">
                        <?php echo htmlspecialchars($gradeData['grade']); ?>
                      </span>
                    </td>
                    <td>
                      <span class="<?php echo $gradeData['remarks'] === 'PASSED' ? 'grade-passed' : 'grade-failed'; ?>">
                        <?php echo htmlspecialchars($gradeData['remarks']); ?>
                      </span>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <!-- Summary Card -->
            <div class="row mt-4">
              <div class="col-md-4">
                <div class="info-card text-center">
                  <h5>Total Credits</h5>
                  <h3 class="text-primary mb-0"><?php echo $totalCredits; ?></h3>
                  <small class="text-muted">Credits Enrolled</small>
                </div>
              </div>
              <div class="col-md-4">
                <div class="info-card text-center">
                  <h5>Current GPA</h5>
                  <h3 class="text-success mb-0"><?php echo number_format($gpa, 2); ?></h3>
                  <small class="text-muted">Out of 4.0 Scale</small>
                </div>
              </div>
              <div class="col-md-4">
                <div class="info-card text-center">
                  <h5>Courses Completed</h5>
                  <h3 class="text-info mb-0"><?php echo count($grades); ?></h3>
                  <small class="text-muted">All Passed</small>
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

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  
  <!-- Student Profile JS -->
  <script src="assets/js/student-profile.js"></script>
  
  <!-- Dark Mode JS -->
  <script src="assets/js/dark-mode.js"></script>
</body>
</html>

