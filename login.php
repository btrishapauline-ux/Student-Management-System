<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Student Management System</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/logo.png" rel="icon" type="image/png">
  <link href="assets/img/logo.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Jost:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/login.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- =======================================================
  * Template Name: Arsha
  * Template URL: https://bootstrapmade.com/arsha-free-bootstrap-html-template-corporate/
  * Updated: Feb 22 2025 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<?php
session_start();
require_once('db.php');

// Handle student login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $_SESSION['login_error'] = 'Please enter both email/username and password.';
        header('Location: login.php');
        exit();
    }

    // Allow login by username or student_email
    $sql = "SELECT sl.student_id, sl.password, s.firstname, s.lastname
            FROM student_login sl
            JOIN students s ON s.student_id = sl.student_id
            WHERE sl.username = ? OR sl.student_email = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log('Login prepare failed: ' . $conn->error);
        $_SESSION['login_error'] = 'Server error. Please try again.';
        header('Location: login.php');
        exit();
    }

    $stmt->bind_param('ss', $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['student_id'];
        $_SESSION['user_type'] = 'student';
        $_SESSION['login_success'] = 'Welcome back, ' . htmlspecialchars($row['firstname'] . ' ' . $row['lastname']);
        header('Location: profile.php');
        exit();
    }

    $_SESSION['login_error'] = 'Invalid credentials. Please try again.';
    header('Location: login.php');
    exit();
}
?>
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

      <a class="btn-getstarted" href="signup.php">Sign Up</a>

    </div>
  </header>
<!--LOG IN FORM-->
<main>
    <div class="login-box">
        <div class="box-left">
            <div class="welcome-content">
                <div class="logo">
                    <i class="fas fa-user-circle"></i>
                    <h2>Welcome Back</h2>
                </div>
                <p>Sign in to access your account and manage your profile.</p>
                
                <div class="benefits">
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Secure & encrypted</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>No spam, ever</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Access all features</span>
                    </div>
                </div>
                
                <div class="testimonial">
                    <p>"This is the most seamless login experience I've ever had."</p>
                    <div class="author">
                        <strong>Alex Johnson</strong>
                        <span>Product Manager</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="box-right">
            <?php
            // Show login errors if any
            if (isset($_SESSION['login_error'])) {
                echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
                unset($_SESSION['login_error']);
            }
            if (isset($_SESSION['login_success'])) {
                echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['login_success']) . '</div>';
                unset($_SESSION['login_success']);
            }
            ?>
            <form id="loginForm" class="login-form" action="login.php" method="POST" novalidate>
                <h2>Sign In to Your Account</h2>
                <p class="form-subtitle">Enter your details below</p>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <div class="input-with-icon">
                        <input type="email" id="email" name="username" placeholder="name@example.com" required>
                        <i class="fas fa-user input-icon"></i>
                    </div>
                    <div class="error-message" id="emailError"></div>
                </div>
                
                <div class="form-group">
                    <div class="label-row">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <a href="#" class="forgot-link">Forgot password?</a>
                    </div>
                    <div class="input-with-icon">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <i class="fas fa-key input-icon"></i>
                        <button type="button" class="toggle-password" id="togglePassword" onclick="togglePasswordVisibility('password', 'togglePassword')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="error-message" id="passwordError"></div>
                </div>
                
                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember">
                        <label for="remember">Keep me signed in</label>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">
                    <span>Sign In</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
                
                <div class="divider">
                    <span>or sign in with</span>
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
                    <p>New user? <a href="signup.php">Create an account</a></p>
                </div>
            </form>
            
            <div class="login-success" id="successMessage">
                <div class="success-content">
                    <i class="fas fa-check-circle"></i>
                    <h3>Login Successful!</h3>
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

    <script src="script.js"></script>
  </main>

    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Bicol University</strong> <span>All Rights Reserved</span></p>
      <div class="credits">
        <!-- All the links in the footer should remain intact. -->
        <!-- You can delete the links only if you've purchased the pro version. -->
        <!-- Licensing information: https://bootstrapmade.com/license/ -->
        <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
        Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
      </div>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/waypoints/noframework.waypoints.js"></script>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

  <!-- Main JS File -->
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