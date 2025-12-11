<?php
session_start();
require_once('db.php'); // Include the secure database connection

// Function to handle redirection and session error logging
function redirect_with_error($message) {
    $_SESSION['signup_error'] = $message;
    // FIX: Changed redirect target to match the file name (signup.php)
    header("location: signup.php"); 
    exit();
}

// Check if the request is a POST submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

// --- 1. Retrieve and Validate Data ---
$firstname        = trim($_POST['firstname'] ?? '');
$lastname         = trim($_POST['lastname'] ?? '');
$student_id_num   = trim($_POST['student_id'] ?? ''); // username for login (string)
$email            = trim($_POST['email'] ?? '');
$course           = $_POST['course'] ?? '';
$year_level       = $_POST['year_level'] ?? '';
$password         = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$terms_agreed     = isset($_POST['terms']); 

// Allowed values to match ENUMs in DB
$allowed_courses = [
    'BS Information Technology',
    'BS Computer Science',
    'BS Electrical Engineering',
    'BS Mechanical Engineering',
    'BS Education',
    'BS Nursing',
    'BS Business Administration',
    'BS Accountancy'
];
$allowed_years = ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'];

// Basic input validation
if ($firstname === '' || $lastname === '' || $student_id_num === '' || $email === '' || $course === '' || $year_level === '' || $password === '' || $confirm_password === '') {
    redirect_with_error("All required fields must be filled.");
}
if (!in_array($course, $allowed_courses, true)) {
    redirect_with_error("Invalid course selected.");
}
if (!in_array($year_level, $allowed_years, true)) {
    redirect_with_error("Invalid year level selected.");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_error("Please enter a valid email address.");
}
if ($password !== $confirm_password) {
    redirect_with_error("Password and Confirm Password do not match.");
}
if (!$terms_agreed) {
    redirect_with_error("You must agree to the Terms of Service.");
}

// --- 2. Check for Existing User (Security Check) ---
// Check if username or student_email already exists in student_login
$check_login_sql = "SELECT 1 FROM student_login WHERE username = ? OR student_email = ? LIMIT 1";
$stmt = $conn->prepare($check_login_sql);
if (!$stmt) {
    redirect_with_error("Server error. Please try again.");
}
$stmt->bind_param("ss", $student_id_num, $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $stmt->close();
    redirect_with_error("An account already exists with this Student ID or Email.");
}
$stmt->close();

// Check if email already exists in students table (unique constraint)
$check_student_email = "SELECT 1 FROM students WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($check_student_email);
if (!$stmt) {
    redirect_with_error("Server error. Please try again.");
}
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $stmt->close();
    redirect_with_error("An account already exists with this email.");
}
$stmt->close();

// --- 3. Hash the Password (CRITICAL SECURITY STEP) ---
// Use a secure, slow hashing algorithm (Argon2 or Bcrypt via PASSWORD_DEFAULT)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
if ($hashed_password === false) {
    // Should almost never happen, but essential error check
    redirect_with_error("Failed to hash the password. Please try again.");
}

// --- 4. START TRANSACTION ---
// Since we are inserting into two tables, we use a transaction to ensure both succeed or both fail.
$conn->begin_transaction();
$success = true;

try {
    // --- STEP A: Insert into `students` table ---
    // Note: The `added_by` field is nullable, so we omit it for student self-registration
    $sql_students = "INSERT INTO students (firstname, lastname, email, course, year_level) VALUES (?, ?, ?, ?, ?)";
    $stmt_students = $conn->prepare($sql_students);

    // Bind parameters (s=string)
    $stmt_students->bind_param("sssss", $firstname, $lastname, $email, $course, $year_level);
    
    if (!$stmt_students->execute()) {
        throw new Exception("Student record insertion failed: " . $stmt_students->error);
    }
    
    // Get the ID of the new student record (needed for the next table)
    $new_student_id = $conn->insert_id;
    $stmt_students->close();

    // --- STEP B: Insert into `student_login` table ---
    // The student_id links the login credentials to the student details
    $sql_login = "INSERT INTO student_login (student_id, username, student_email, course, year_level, password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_login = $conn->prepare($sql_login);
    
    // Bind parameters (i=integer, s=string)
    $stmt_login->bind_param("isssss", $new_student_id, $student_id_num, $email, $course, $year_level, $hashed_password);

    if (!$stmt_login->execute()) {
        throw new Exception("Student login creation failed: " . $stmt_login->error);
    }
    
    $stmt_login->close();

    // If both steps succeeded, commit the transaction
    $conn->commit();

    // --- 5. SUCCESS: Set Session and Redirect ---
    $_SESSION['user_id'] = $new_student_id;
    $_SESSION['user_type'] = 'student';
    $_SESSION['signup_success'] = "Account successfully created!"; // Optional success message

    // Redirect to the student dashboard
    header("location: profile.php"); 
    exit();

} catch (Exception $e) {
    // An error occurred, rollback all insertions
    $conn->rollback();
    
    // Log the error (not shown to user) and redirect
    error_log("Signup Error: " . $e->getMessage());
    redirect_with_error("Account creation failed due to a server error. Please try again.");
}

} // Close the POST if block

// Close the connection
$conn->close();
?>






<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Student Management System | Sign Up</title>
  <meta name="description" content="Student Sign Up - Bicol University">
  <meta name="keywords" content="">

  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Jost:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <link href="assets/css/login.css" rel="stylesheet">
  <link href="assets/css/SignUp.css" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  </head>

<body class="starter-page-page">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="index.html" class="logo d-flex align-items-center me-auto">
        <h1 class="sitename">BICOL UNIVERSITY</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.html">Home</a></li>
          <li><a href="#about">About</a></li>
          <li><a href="#services">Services</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-getstarted" href="login.php">Login</a>

    </div>
  </header>

  <main>
    <div class="login-box">
      <div class="box-left">
        <div class="welcome-content">
          <div class="logo">
            <i class="fas fa-user-plus"></i>
            <h2>Join Our Community</h2>
          </div>
          <p>Create your student account to access all university services and resources.</p>
          
          <div class="benefits">
            <div class="benefit-item">
              <i class="fas fa-check-circle"></i>
              <span>Access course materials</span>
            </div>
            <div class="benefit-item">
              <i class="fas fa-check-circle"></i>
              <span>Track your progress</span>
            </div>
            <div class="benefit-item">
              <i class="fas fa-check-circle"></i>
              <span>Connect with faculty</span>
            </div>
            <div class="benefit-item">
              <i class="fas fa-check-circle"></i>
              <span>University email account</span>
            </div>
            <div class="benefit-item">
              <i class="fas fa-check-circle"></i>
              <span>Library resources access</span>
            </div>
          </div>
          
          <div class="testimonial">
            <p>"The student portal made my university journey so much easier to manage!"</p>
            <div class="author">
              <strong>Maria Santos</strong>
              <span>3rd Year, Computer Science</span>
            </div>
          </div>
        </div>
      </div>
      
      <div class="box-right">
        <form id="signupForm" class="login-form" action="signup.php" method="POST">
          <h2>Create Student Account</h2>
          <p class="form-subtitle">Enter your student details</p>

          <?php
          if (isset($_SESSION['signup_error'])) {
              echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['signup_error']) . '</div>';
              unset($_SESSION['signup_error']); // Clear the message after display
          }
          if (isset($_SESSION['signup_success'])) {
              // Note: Success case will redirect to profile.php, so this is mostly for debugging
              echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['signup_success']) . '</div>';
              unset($_SESSION['signup_success']);
          }
          ?>
          
          <div class="form-group">
            <label for="fullName">
              <i class="fas fa-user"></i> Full Name *
            </label>
            <div class="input-with-icon row">
              <div class="col-md-6">
                  <input type="text" id="firstName" name="firstname" placeholder="First Name" required>
              </div>
              <div class="col-md-6">
                  <input type="text" id="lastName" name="lastname" placeholder="Last Name" required>
              </div>
              <i class="fas fa-user input-icon"></i>
            </div>
            <div class="error-message" id="nameError"></div>
          </div>
          
          <div class="form-group">
            <label for="studentId">
              <i class="fas fa-id-card"></i> Student ID *
            </label>
            <div class="input-with-icon">
              <input type="text" id="studentId" name="student_id" placeholder="2023-00123" required>
              <i class="fas fa-id-badge input-icon"></i>
            </div>
            <div class="error-message" id="idError"></div>
          </div>
          
          <div class="form-group">
            <label for="email">
              <i class="fas fa-envelope"></i> University Email *
            </label>
            <div class="input-with-icon">
              <input type="email" id="email" name="email" placeholder="student@bicol-u.edu.ph" required>
              <i class="fas fa-at input-icon"></i>
            </div>
            <small class="form-hint">Use your university-provided email address</small>
            <div class="error-message" id="emailError"></div>
          </div>
          
          <div class="form-group">
            <label for="course">
              <i class="fas fa-graduation-cap"></i> Course/Program *
            </label>
            <div class="input-with-icon">
              <select id="course" name="course" required>
                <option value="" disabled selected>Select your course</option>
                <option value="BS Information Technology">BS Information Technology</option>
                <option value="BS Computer Science">BS Computer Science</option>
                <option value="BS Electrical Engineering">BS Electrical Engineering</option>
                <option value="BS Mechanical Engineering">BS Mechanical Engineering</option>
                <option value="BS Education">BS Education</option>
                <option value="BS Nursing">BS Nursing</option>
                <option value="BS Business Administration">BS Business Administration</option>
                <option value="BS Accountancy">BS Accountancy</option>
              </select>
              <i class="fas fa-graduation-cap input-icon"></i>
            </div>
            <div class="error-message" id="courseError"></div>
          </div>
          
          <div class="form-group">
            <label for="yearLevel">
              <i class="fas fa-calendar-alt"></i> Year Level *
            </label>
            <div class="input-with-icon">
              <select id="yearLevel" name="year_level" required>
                <option value="" disabled selected>Select year level</option>
                <option value="1st Year">1st Year</option>
                <option value="2nd Year">2nd Year</option>
                <option value="3rd Year">3rd Year</option>
                <option value="4th Year">4th Year</option>
                <option value="5th Year">5th Year</option>
              </select>
              <i class="fas fa-calendar input-icon"></i>
            </div>
          </div>
          
          <div class="form-group">
            <div class="label-row">
              <label for="password">
                <i class="fas fa-lock"></i> Password *
              </label>
            </div>
            <div class="input-with-icon">
              <input type="password" id="password" name="password" placeholder="Create a strong password" required>
              <i class="fas fa-key input-icon"></i>
              <button type="button" class="toggle-password" id="togglePassword">
                <i class="fas fa-eye"></i>
              </button>
            </div>
            <small class="form-hint">Minimum 8 characters with uppercase, number, and special character</small>
            <div class="error-message" id="passwordError"></div>
          </div>
          
          <div class="form-group">
            <label for="confirmPassword">
              <i class="fas fa-lock"></i> Confirm Password *
            </label>
            <div class="input-with-icon">
              <input type="password" id="confirmPassword" name="confirm_password" placeholder="Re-enter your password" required>
              <i class="fas fa-key input-icon"></i>
            </div>
            <div class="error-message" id="confirmPasswordError"></div>
          </div>
          
          <div class="form-options">
            <div class="remember-me">
              <input type="checkbox" id="terms" name="terms" required>
              <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a> *</label>
            </div>
            <div class="remember-me">
              <input type="checkbox" id="newsletter">
              <label for="newsletter">Receive university updates and announcements</label>
            </div>
          </div>
          
          <button type="submit" class="submit-btn">
            <span>Create Account</span>
            <i class="fas fa-user-plus"></i>
          </button>
          
          <div class="divider">
            <span>or sign up with</span>
          </div>
          
          <div class="social-buttons">
            <button type="button" class="social-btn google">
              <i class="fab fa-google"></i>
              <span>Google</span>
            </button>
            <button type="button" class="social-btn microsoft">
              <i class="fab fa-microsoft"></i>
              <span>Microsoft</span>
            </button>
          </div>
          
          <div class="signup-link">
            <p>Already have an account? <a href="login.html">Log In</a></p>
          </div>
        </form>
        
        <div class="login-success" id="successMessage">
          <div class="success-content">
            <i class="fas fa-check-circle"></i>
            <h3>Account Created Successfully!</h3>
            <p>Redirecting to your dashboard...</p>
          </div>
        </div>
      </div>
      
      <div class="box-decoration">
        <div class="decoration-circle circle-1"></div>
        <div class="decoration-circle circle-2"></div>
        <div class="decoration-circle circle-3"></div>
      </div>
    </div>
  </main>

  <footer id="footer" class="footer">
    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Bicol University</strong> <span>All Rights Reserved</span></p>
      <div class="credits">
        Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
      </div>
    </div>
  </footer>

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <div id="preloader"></div>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/waypoints/noframework.waypoints.js"></script>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

  <script src="assets/js/main.js"></script>


</body>

</html>