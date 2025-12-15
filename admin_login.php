<?php
// Start the session to store user login status
session_start();
require_once('db.php'); // Your database connection file (must create $conn object)

// Check for submission errors from the last attempt (optional, but good UX)
$error_message = '';
if (isset($_SESSION['login_error'])) {
    $error_message = $_SESSION['login_error'];
    unset($_SESSION['login_error']); // Clear the error after displaying/using it
}
if (isset($_SESSION['login_success'])) {
    unset($_SESSION['login_success']); // Clear any stale success message
}

// Check if the form was submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Retrieve and Sanitize Input
    // Use the 'name' attributes from the HTML form
    $username = trim($_POST['username']);
    $password = $_POST['password']; // Plain password

    // Check if fields are empty
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Please enter both username and password.";
        header("location:admin_login.php");
        exit();
    }
    
    // 2. Prepare the SQL statement
    // Select the password hash and the admin_id from the admin_login table
    // based on the submitted username.
    $sql = "SELECT admin_id, password FROM admin_login WHERE username = ?";

    $stmt = $conn->prepare($sql);

    // 3. Bind the parameter (s = string for username)
    $stmt->bind_param("s", $username);

    // 4. Execute the statement
    $stmt->execute();

    // 5. Get the result
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    // 6. Verification and Session Creation
    if ($row) {
        // A user was found. Now verify the password hash.
        $hashed_password = $row['password'];

        // Use the secure password_verify() function
        if (password_verify($password, $hashed_password)) {
            
            // Success! Set session variables.
            $_SESSION['user_id'] = $row['admin_id'];
            $_SESSION['user_type'] = 'admin'; // CRITICAL for your session.php file

            // Redirect to the admin dashboard
            header("location:admin.php"); 
            exit();

        } else {
            // Invalid Password
            $error = "Invalid username or password.";
        }
    } else {
        // No user found with that username
        $error = "Invalid username or password.";
    }

    // 7. Handle Login Failure
    // Store the error message and redirect back to the login page.
    $_SESSION['login_error'] = $error;
    header("location:admin_login.php");
    exit();

} 
?>




<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Admin Portal - Student Management System</title>
  <meta name="description" content="Admin login for Student Management System">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

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

  <!-- CSS Files -->
  <link href="assets/css/main.css" rel="stylesheet">
  <link href="assets/css/login.css" rel="stylesheet">
  <link href="assets/css/admin_login.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- =======================================================
  * Template Name: Arsha
  * Template URL: https://bootstrapmade.com/arsha-free-bootstrap-html-template-corporate/
  * Updated: Feb 22 2025 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="starter-page-page admin-login-page">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <!-- <img src="assets/img/logo.webp" alt=""> -->
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

      <a class="btn-getstarted" href="login.php">Sign Up</a>

    </div>
  </header>

  <!-- ADMIN LOGIN FORM -->
  <main>
    <div class="login-container">
      <div class="login-box admin-login-box">
        <div class="box-left admin-box-left">
          <div class="welcome-content">
            <div class="logo">
              <i class="fas fa-user-shield"></i>
              <h2>Admin Portal</h2>
            </div>
            <p class="admin-subtitle">Secure access to system administration and management tools.</p>
            
            <div class="admin-features">
              <div class="feature-item">
                <i class="fas fa-shield-alt"></i>
                <div>
                  <h4>Enhanced Security</h4>
                  <p>Multi-factor authentication and encrypted sessions</p>
                </div>
              </div>
              <div class="feature-item">
                <i class="fas fa-chart-line"></i>
                <div>
                  <h4>Analytics Dashboard</h4>
                  <p>Comprehensive system analytics and reporting</p>
                </div>
              </div>
              <div class="feature-item">
                <i class="fas fa-cogs"></i>
                <div>
                  <h4>System Management</h4>
                  <p>Full control over users, courses, and settings</p>
                </div>
              </div>
            </div>
            
            <div class="security-notice">
              <i class="fas fa-exclamation-triangle"></i>
              <p>This portal is restricted to authorized personnel only. Unauthorized access is prohibited.</p>
            </div>
          </div>
        </div>
        
        <div class="box-right admin-box-right">
          <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
              <?php echo htmlspecialchars($error_message); ?>
            </div>
          <?php endif; ?>
          <form id="adminLoginForm" class="login-form admin-login-form" action="admin_login.php" method="POST">
            <div class="form-header">
              <h2>Administrator Login</h2>
              <p class="form-subtitle">Enter your admin credentials</p>
            </div>
            
            <div class="form-group">
              <label for="adminUsername">
                <i class="fas fa-user-tie"></i> Admin Username
              </label>
              <div class="input-with-icon">
                <input type="text" id="adminUsername" name="username" placeholder="Enter admin username" required>
                <i class="fas fa-user input-icon"></i>
              </div>
              <div class="error-message" id="usernameError"></div>
            </div>
            
            <div class="form-group">
              <div class="label-row">
                <label for="adminPassword">
                  <i class="fas fa-lock"></i> Password
                </label>
                <a href="admin-forgot-password.html" class="forgot-link">Forgot password?</a>
              </div>
              <div class="input-with-icon">
                <input type="password" id="adminPassword" name="password" placeholder="Enter admin password" required>
                <i class="fas fa-key input-icon"></i>
                <button type="button" class="toggle-password" id="toggleAdminPassword">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
              <div class="error-message" id="passwordError"></div>
            </div>
            
            <div class="form-group">
              <label for="adminCode">
                <i class="fas fa-key"></i> Security Code (Optional)
              </label>
              <div class="input-with-icon">
                <input type="text" id="adminCode" placeholder="Enter security code if required">
                <i class="fas fa-shield-alt input-icon"></i>
              </div>
              <div class="form-note">
                <i class="fas fa-info-circle"></i> Security code is only required for first-time login or from new devices
              </div>
            </div>
            
            <div class="form-options">
              <div class="remember-me">
                <input type="checkbox" id="rememberAdmin">
                <label for="rememberAdmin">Keep me signed in</label>
              </div>
              <div class="secure-session">
                <input type="checkbox" id="secureSession" checked>
                <label for="secureSession">Enable secure session</label>
              </div>
            </div>
            
            <button type="submit" class="submit-btn admin-submit-btn">
              <span>Access Admin Panel</span>
              <i class="fas fa-arrow-right-to-bracket"></i>
            </button>
            
            <div class="divider">
              <span>or authenticate with</span>
            </div>
            
            <div class="social-buttons">
              <button type="button" class="social-btn admin-social-btn microsoft">
                <i class="fab fa-microsoft"></i>
                <span>Microsoft AD</span>
              </button>
              <button type="button" class="social-btn admin-social-btn google">
                <i class="fab fa-google"></i>
                <span>Google Workspace</span>
              </button>
            </div>
            
            <div class="login-footer">
              <div class="signup-link">
                <p>Need admin access? <a href="admin-request.html">Request credentials</a></p>
              </div>
              <div class="back-link">
                <a href="#" onclick="window.location.href='LogIn.html'; return false;">
                  <i class="fas fa-arrow-left"></i> Back to Student Login
                </a>
              </div>
            </div>
          </form>
          
          <div class="login-success admin-login-success" id="adminSuccessMessage">
            <div class="success-content">
              <i class="fas fa-shield-check"></i>
              <h3>Authentication Successful!</h3>
              <p>Admin privileges verified. Redirecting to dashboard...</p>
              <div class="loading-spinner"></div>
            </div>
          </div>
        </div>
        
        <div class="box-decoration admin-box-decoration">
          <div class="decoration-circle admin-circle circle-1"></div>
          <div class="decoration-circle admin-circle circle-2"></div>
          <div class="decoration-circle admin-circle circle-3"></div>
        </div>
      </div>
    </div>
  </main>

  <footer id="footer" class="footer position-relative">
    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Bicol University</strong> <span>All Rights Reserved</span></p>
      <div class="credits">
        <div class="admin-footer-note">
          <i class="fas fa-lock"></i> 
          <span>This is a secure administrative system. All activities are logged and monitored.</span>
        </div>
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
  
  <!-- Admin Login JS -->
  <script src="assets/js/admin-login.js"></script>

</body>

</html>