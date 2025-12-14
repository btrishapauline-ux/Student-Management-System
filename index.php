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
    <section id="hero" class="hero section dark-background">

      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center" data-aos="zoom-out">
            <h1>STUDENT MANAGEMENT SYSTEM</h1>
            <div class="d-flex">
              <a href="#" class="btn-get-started" id="heroLoginTriggerBtn">Get Started</a>
              <a href="#" class="btn-get-started" id="heroLoginTriggerBtn">Sign In</a>
            
            </div>
          </div>
          <div class="col-lg-6 order-1 order-lg-2 hero-img" data-aos="zoom-out" data-aos-delay="200">
            <img src="assets/img/hero-img.png" class="img-fluid animated" alt="">
          </div>
        </div>
      </div>

    </section><!-- /Hero Section -->

    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Bicol University</strong> <span>All Rights Reserved</span></p>
      <div class="credits">
        <!-- All the links in the footer should remain intact. -->
        <!-- You can delete the links only if you've purchased the pro version. -->
        <!-- Licensing information: https://bootstrapmade.com/license/ -->
        <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
        
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