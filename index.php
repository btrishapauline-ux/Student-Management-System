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
  <link href="assets/css/main.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: Arsha
  * Template URL: https://bootstrapmade.com/arsha-free-bootstrap-html-template-corporate/
  * Updated: Feb 22 2025 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <img src="assets/img/logo.png" alt="Bicol University Logo" style="height: 40px; margin-right: 10px;">
        <h1 class="sitename">BICOL UNIVERSITY</h1>
      </a>


      <a class="btn-getstarted" href="login.php">Sign In</a>

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section university-hero">
      <div class="hero-overlay"></div>
      <div class="container">
        <div class="row gy-4 align-items-center">
          <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center" data-aos="fade-right">
            <div class="hero-badge mb-3">
              <span><i class="bi bi-mortarboard"></i> Bicol University</span>
            </div>
            <h1 class="hero-title">Student Management System</h1>
            <p class="hero-subtitle">Empowering academic excellence through innovative digital solutions. Manage your academic journey with ease and efficiency.</p>
            <div class="hero-buttons d-flex gap-3 flex-wrap">
              <a href="#" class="btn-get-started btn-primary-university" id="heroLoginTriggerBtn">
                <i class="bi bi-box-arrow-in-right me-2"></i>Get Started
              </a>
              <a href="signup.php" class="btn-get-started btn-outline-university">
                <i class="bi bi-person-plus me-2"></i>Create Account
              </a>
            </div>
            <div class="hero-stats mt-5">
              <div class="row g-4">
                <div class="col-4">
                  <div class="stat-item">
                    <h3 class="stat-number">100+</h3>
                    <p class="stat-label">Students</p>
                  </div>
                </div>
                <div class="col-4">
                  <div class="stat-item">
                    <h3 class="stat-number">8</h3>
                    <p class="stat-label">Courses</p>
                  </div>
                </div>
                <div class="col-4">
                  <div class="stat-item">
                    <h3 class="stat-number">24/7</h3>
                    <p class="stat-label">Support</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 order-1 order-lg-2 hero-img" data-aos="fade-left" data-aos-delay="200">
            <div class="hero-image-wrapper">
              <img src="assets/img/hero-img.png" class="img-fluid animated" alt="Student Management System">
              <div class="hero-decoration"></div>
            </div>
          </div>
        </div>
      </div>
    </section><!-- /Hero Section -->

    <!-- Features Section -->
    <section id="features" class="features section light-background">
      <div class="container">
        <div class="section-header text-center mb-5" data-aos="fade-up">
          <span class="section-badge">Features</span>
          <h2 class="section-title">Everything You Need</h2>
          <p class="section-description">Comprehensive tools to manage your academic life efficiently</p>
        </div>

        <div class="row gy-4">
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="feature-card university-card">
              <div class="feature-icon">
                <i class="bi bi-person-badge"></i>
              </div>
              <h4>Student Profile</h4>
              <p>Manage your personal information, upload profile pictures, and keep your records up to date.</p>
            </div>
          </div>

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="feature-card university-card">
              <div class="feature-icon">
                <i class="bi bi-journal-bookmark"></i>
              </div>
              <h4>Course Management</h4>
              <p>View all your enrolled courses, track progress, and access course materials easily.</p>
            </div>
          </div>

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
            <div class="feature-card university-card">
              <div class="feature-icon">
                <i class="bi bi-graph-up"></i>
              </div>
              <h4>Grade Tracking</h4>
              <p>Monitor your academic performance with detailed grade reports and GPA calculations.</p>
            </div>
          </div>

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
            <div class="feature-card university-card">
              <div class="feature-icon">
                <i class="bi bi-calendar-week"></i>
              </div>
              <h4>Class Schedule</h4>
              <p>Access your complete class schedule, exam dates, and important academic deadlines.</p>
            </div>
          </div>

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
            <div class="feature-card university-card">
              <div class="feature-icon">
                <i class="bi bi-shield-lock"></i>
              </div>
              <h4>Secure Access</h4>
              <p>Your data is protected with industry-standard security measures and encrypted sessions.</p>
            </div>
          </div>

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
            <div class="feature-card university-card">
              <div class="feature-icon">
                <i class="bi bi-phone"></i>
              </div>
              <h4>Mobile Responsive</h4>
              <p>Access your account from any device - desktop, tablet, or mobile phone.</p>
            </div>
          </div>
        </div>
      </div>
    </section><!-- /Features Section -->

    <!-- About Section -->
    <section id="about" class="about section">
      <div class="container">
        <div class="row gy-4 align-items-center">
          <div class="col-lg-6" data-aos="fade-right">
            <div class="about-image-wrapper">
              <img src="assets/img/bg/campus.jpg" class="img-fluid rounded" alt="Bicol University Campus">
              <div class="about-badge">
                <i class="bi bi-award"></i>
                <span>Excellence in Education</span>
              </div>
            </div>
          </div>
          <div class="col-lg-6" data-aos="fade-left">
            <div class="section-header mb-4">
              <span class="section-badge">About Us</span>
              <h2 class="section-title">Bicol University Student Management System</h2>
            </div>
            <p class="lead">A comprehensive digital platform designed to streamline academic management and enhance the educational experience.</p>
            <ul class="about-list">
              <li><i class="bi bi-check-circle-fill"></i> Centralized student information management</li>
              <li><i class="bi bi-check-circle-fill"></i> Real-time grade and course tracking</li>
              <li><i class="bi bi-check-circle-fill"></i> Secure and user-friendly interface</li>
              <li><i class="bi bi-check-circle-fill"></i> 24/7 access to academic records</li>
            </ul>
            <p>Our Student Management System provides students and administrators with powerful tools to manage academic records, track progress, and stay organized throughout their educational journey.</p>
          </div>
        </div>
      </div>
    </section><!-- /About Section -->

  </main>

  <footer id="footer" class="footer university-footer">
    <div class="container">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6">
          <div class="footer-info">
            <a href="index.php" class="logo d-flex align-items-center mb-3">
              <img src="assets/img/logo.png" alt="Bicol University Logo" style="height: 40px; margin-right: 10px;">
              <span class="sitename">BICOL UNIVERSITY</span>
            </a>
            <p>Comprehensive Student Management System for efficient academic record management and student services.</p>
            <div class="social-links mt-3">
              <a href="#" class="twitter"><i class="bi bi-twitter"></i></a>
              <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
              <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
              <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
            </div>
          </div>
        </div>

        <div class="col-lg-2 col-md-6 footer-links">
          <h4>Quick Links</h4>
          <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="#features">Features</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="login.php">Student Login</a></li>
            <li><a href="admin_login.php">Admin Login</a></li>
          </ul>
        </div>

        <div class="col-lg-3 col-md-6 footer-links">
          <h4>Services</h4>
          <ul>
            <li><a href="course.php">Course Management</a></li>
            <li><a href="grade.php">Grade Tracking</a></li>
            <li><a href="schedule.php">Class Schedule</a></li>
            <li><a href="profile.php">Student Profile</a></li>
            <li><a href="admin.php">Admin Dashboard</a></li>
          </ul>
        </div>

        <div class="col-lg-3 col-md-6 footer-contact">
          <h4>Contact Us</h4>
          <p>
            Bicol University<br>
            Legazpi City, Albay<br>
            Philippines <br><br>
            <strong>Phone:</strong> (052) 742-1234<br>
            <strong>Email:</strong> info@bicol-u.edu.ph<br>
          </p>
        </div>
      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Bicol University</strong> <span>All Rights Reserved</span></p>
      <div class="credits">
        Student Management System v2.0 | Designed with <i class="bi bi-heart text-danger"></i> for Bicol University
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
  
  <!-- Dark Mode JS -->
  <script src="assets/js/dark-mode.js"></script>


  <!-- Login Options Modal -->
<div class="login-options-modal" id="loginOptionsModal">
  <div class="login-modal-content">
    <div class="login-modal-header">
      <h3><i class="fas fa-sign-in-alt"></i> Choose Login Method</h3>
      <button class="login-modal-close" id="loginModalCloseBtn">
        <i class="fas fa-times"></i>
      </button>
    </div>
    
    <div class="login-modal-body">
      <div class="login-option-card primary-login">
        <div class="login-option-icon">
          <i class="fas fa-user-graduate"></i>
        </div>
        <div class="login-option-content">
          <h4>Student Login</h4>
          <p>Access your student dashboard, courses, and grades</p>
        </div>
        <button class="login-select-btn" onclick="window.location.href='login.php?type=student'">
          Login as Student
        </button>
      </div>
      
      
      <div class="login-option-card admin-login">
        <div class="login-option-icon">
          <i class="fas fa-user-tie"></i>
        </div>
        <div class="login-option-content">
          <h4>Administrator Login</h4>
          <p>System administration and management tools</p>
        </div>
        <button class="login-select-btn" onclick="window.location.href='admin_login.php?type=admin'">
          Login as Admin
        </button>
      </div>
      
    
    <div class="login-modal-footer">
      <p>Need help? <a href="#">Contact Support</a></p>
      <div class="login-help-links">
        <a href="#"><i class="fas fa-key"></i> Forgot Password</a>
        <a href="#"><i class="fas fa-user-plus"></i> Create Account</a>
        <a href="#"><i class="fas fa-question-circle"></i> Help Center</a>
      </div>
    </div>
  </div>
</div>
</body>

</html>