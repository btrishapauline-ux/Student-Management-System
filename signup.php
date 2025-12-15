<?php
session_start();
require_once('db.php');
require_once('notification_helper.php');

// Enable all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to handle redirection and session error logging
function redirect_with_error($message) {
    $_SESSION['signup_error'] = $message;
    header("location: signup.php");
    exit();
}

// Check if the request is a POST submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // --- 1. Retrieve and Validate Data ---
    $firstname        = trim($_POST['firstname'] ?? '');
    $lastname         = trim($_POST['lastname'] ?? '');
    $student_id_num   = trim($_POST['student_id'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $course           = $_POST['course'] ?? '';
    $year_level       = $_POST['year_level'] ?? '';
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms_agreed     = isset($_POST['terms']); 

    // Debug: Show what we received
    echo "<pre>DEBUG - Form Data Received:\n";
    echo "Firstname: '$firstname'\n";
    echo "Lastname: '$lastname'\n";
    echo "Student ID: '$student_id_num'\n";
    echo "Email: '$email'\n";
    echo "Course: '$course'\n";
    echo "Year Level: '$year_level'\n";
    echo "Password length: " . strlen($password) . "\n";
    echo "Terms: " . ($terms_agreed ? 'Yes' : 'No') . "\n";
    echo "</pre>";

    // Allowed values to match ENUMs in DB (EXACTLY as in your table)
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
    
    // Check if course is valid
    if (!in_array($course, $allowed_courses, true)) {
        echo "<pre>DEBUG - Course validation failed.\n";
        echo "Submitted: '$course'\n";
        echo "Allowed: " . print_r($allowed_courses, true) . "\n";
        echo "Match check: " . (in_array($course, $allowed_courses) ? 'Yes' : 'No') . "\n";
        echo "</pre>";
        redirect_with_error("Invalid course selected: '$course'");
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

    // Check if email already exists in students table
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

    // --- 3. Hash the Password ---
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    if ($hashed_password === false) {
        redirect_with_error("Failed to hash the password. Please try again.");
    }

    echo "<pre>DEBUG - Starting database operations...\n";
    
    // --- 4. Insert into database WITHOUT transaction first ---
    try {
        // Step A: Insert into students table
        echo "Step 1: Inserting into students table...\n";
        $sql_students = "INSERT INTO students (firstname, lastname, course, year_level, email)
                         VALUES (?, ?, ?, ?, ?)";
        
        $stmt_students = $conn->prepare($sql_students);
        if (!$stmt_students) {
            throw new Exception("Student prepare failed: " . $conn->error);
        }
        
        $stmt_students->bind_param("sssss", $firstname, $lastname, $course, $year_level, $email);
        
        if ($stmt_students->execute()) {
            $new_student_id = $conn->insert_id;
            echo "✓ Student inserted! ID: $new_student_id\n";
            $stmt_students->close();
        } else {
            throw new Exception("Student execute failed: " . $stmt_students->error);
        }
        
        // Step B: Insert into student_login table
        echo "\nStep 2: Inserting into student_login table...\n";
        $sql_login = "INSERT INTO student_login (student_id, username, student_email, course, year_level, password)
                      VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt_login = $conn->prepare($sql_login);
        if (!$stmt_login) {
            throw new Exception("Login prepare failed: " . $conn->error);
        }
        
        echo "Binding params: student_id=$new_student_id, username=$student_id_num, email=$email, course=$course, year=$year_level\n";
        
        $stmt_login->bind_param("isssss", $new_student_id, $student_id_num, $email, $course, $year_level, $hashed_password);
        
        if ($stmt_login->execute()) {
            echo "✓ Student login created successfully!\n";
            $stmt_login->close();
        } else {
            throw new Exception("Login execute failed: " . $stmt_login->error);
        }
        
        // Success!
        echo "\n✓ Both inserts successful!\n";
        echo "</pre>";
        
        // Create notification for all admins about new student registration
        $studentName = $firstname . ' ' . $lastname;
        create_notification_for_all_admins(
            'New Student Registration',
            "A new student has registered: {$studentName} ({$course}, {$year_level}). Student ID: {$student_id_num}",
            'info'
        );
        
        $_SESSION['user_id'] = $new_student_id;
        $_SESSION['user_type'] = 'student';
        $_SESSION['signup_success'] = "Account successfully created!";
        $_SESSION['student_id'] = $student_id_num;
        $_SESSION['email'] = $email;

        // Wait 3 seconds to see debug output, then redirect
        echo "<script>
            setTimeout(function() {
                window.location.href = 'profile.php';
            }, 3000);
        </script>";
        exit();
        
    } catch (Exception $e) {
        echo "<pre>✗ ERROR: " . $e->getMessage() . "\n";
        echo "</pre>";
        
        // Don't redirect - let user see the error
        die("Registration failed. Please check the error above and contact support.");
    }

} else {
    // Show the form (HTML part below)
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Student Management Systems | Sign Up</title>
  <meta name="description" content="Student Sign Up - Bicol University">
  <meta name="keywords" content="">
  <link href="assets/img/logo.png" rel="icon" type="image/png">
  <link href="assets/img/logo.png" rel="apple-touch-icon">
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
  
  <!-- Dark Mode CSS -->
  <link href="assets/css/dark-mode.css" rel="stylesheet">
</head>

<body class="starter-page-page">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">
      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <img src="assets/img/logo.png" alt="Bicol University Logo" style="height: 40px; margin-right: 10px;">
        <h1 class="sitename">BICOL UNIVERSITY</h1>
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home</a></li>
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
              unset($_SESSION['signup_error']);
          }
          if (isset($_SESSION['signup_success'])) {
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
              <button type="button" class="toggle-password" id="togglePassword" onclick="togglePasswordVisibility('password', 'togglePassword')">
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
              <button type="button" class="toggle-password" id="toggleConfirmPassword" onclick="togglePasswordVisibility('confirmPassword', 'toggleConfirmPassword')">
                <i class="fas fa-eye"></i>
              </button>
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
            <p>Already have an account? <a href="login.php">Log In</a></p>
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

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/waypoints/noframework.waypoints.js"></script>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="assets/js/main.js"></script>
  
  <script>
    function togglePasswordVisibility(inputId, buttonId) {
        const input = document.getElementById(inputId);
        const button = document.getElementById(buttonId);
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
  </script>
  
  <!-- Dark Mode JS -->
  <script src="assets/js/dark-mode.js"></script>
</body>
</html>