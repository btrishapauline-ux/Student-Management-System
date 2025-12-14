<?php
// Basic session check - if logged in, redirect to profile
session_start();
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'student') {
    header('Location: profile.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Student Portal | Bicol University</title>
  <meta name="description" content="Bicol University Student Management System - Access your courses, grades, schedule and more">
  <meta name="keywords" content="student, portal, bicol university, management system">

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
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
  
  <!-- Dark Mode CSS -->
  <link href="assets/css/dark-mode.css" rel="stylesheet">
  
  <style>
    .hero-section {
      background: linear-gradient(135deg, #37517e 0%, #47b2e4 100%);
      color: white;
      padding: 120px 0 80px;
      position: relative;
      overflow: hidden;
    }
    .hero-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('assets/img/bg/campus.jpg') center/cover;
      opacity: 0.15;
      z-index: 1;
    }
    .hero-content {
      position: relative;
      z-index: 2;
    }
    .feature-card {
      background: white;
      border-radius: 15px;
      padding: 30px;
      height: 100%;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border-top: 4px solid #47b2e4;
    }
    .feature-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    .feature-icon {
      width: 70px;
      height: 70px;
      background: linear-gradient(135deg, #47b2e4 0%, #37517e 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
      color: white;
      font-size: 32px;
    }
    .about-section {
      background: #f8f9fa;
      padding: 80px 0;
    }
    .about-content {
      background: white;
      border-radius: 15px;
      padding: 40px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }
    .stats-card {
      text-align: center;
      padding: 30px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }
    .stats-number {
      font-size: 48px;
      font-weight: 700;
      color: #47b2e4;
      margin-bottom: 10px;
    }
    .cta-section {
      background: linear-gradient(135deg, #37517e 0%, #47b2e4 100%);
      color: white;
      padding: 80px 0;
      text-align: center;
    }
    body.dark-mode .feature-card,
    body.dark-mode .about-content,
    body.dark-mode .stats-card {
      background-color: #2d2d2d;
      color: #e0e0e0;
    }
  </style>
</head>

<body class="index-page">

  <!-- Header -->
  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <!-- Logo -->
      <a href="student_home.php" class="logo d-flex align-items-center me-auto">
        <img src="assets/img/logo.png" alt="Bicol University Logo" style="height: 40px; margin-right: 10px;">
        <h1 class="sitename">BICOL UNIVERSITY</h1>
      </a>

      <!-- Navigation Menu -->
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Home</a></li>
          <li><a href="#features">Features</a></li>
          <li><a href="#about">About BU</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-getstarted" href="login.php">Sign In</a>

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero-section">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6 hero-content" data-aos="fade-right">
            <h1 class="display-4 fw-bold mb-4">Welcome to Bicol University<br>Student Portal</h1>
            <p class="lead mb-4">Your comprehensive platform for managing your academic journey. Access courses, view grades, check schedules, and stay connected with your university.</p>
            <div class="d-flex gap-3 flex-wrap">
              <a href="login.php" class="btn btn-light btn-lg px-4">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
              </a>
              <a href="signup.php" class="btn btn-outline-light btn-lg px-4">
                <i class="bi bi-person-plus me-2"></i>Create Account
              </a>
            </div>
          </div>
          <div class="col-lg-6" data-aos="fade-left">
            <img src="assets/img/hero-img.png" class="img-fluid" alt="Student Portal">
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="section">
      <div class="container">
        <div class="section-header text-center mb-5" data-aos="fade-up">
          <h2>What We Offer</h2>
          <p>Everything you need to manage your academic life in one place</p>
        </div>

        <div class="row g-4">
          <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bi bi-person-vcard"></i>
              </div>
              <h4>Student Profile</h4>
              <p>Manage your personal information, upload profile pictures, and keep your details up to date. Complete your profile to access all features.</p>
              <ul class="list-unstyled mt-3">
                <li><i class="bi bi-check-circle text-success me-2"></i>Personal Information</li>
                <li><i class="bi bi-check-circle text-success me-2"></i>Profile Picture Upload</li>
                <li><i class="bi bi-check-circle text-success me-2"></i>Contact Details</li>
              </ul>
            </div>
          </div>

          <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bi bi-journal-text"></i>
              </div>
              <h4>Course Management</h4>
              <p>View all your enrolled courses, course details, instructors, schedules, and room assignments in one convenient location.</p>
              <ul class="list-unstyled mt-3">
                <li><i class="bi bi-check-circle text-success me-2"></i>Enrolled Courses</li>
                <li><i class="bi bi-check-circle text-success me-2"></i>Course Details</li>
                <li><i class="bi bi-check-circle text-success me-2"></i>Instructor Information</li>
              </ul>
            </div>
          </div>

          <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bi bi-graph-up"></i>
              </div>
              <h4>Grades & Transcripts</h4>
              <p>Track your academic performance with real-time grade updates, GPA calculations, and downloadable transcripts.</p>
              <ul class="list-unstyled mt-3">
                <li><i class="bi bi-check-circle text-success me-2"></i>Real-time Grades</li>
                <li><i class="bi bi-check-circle text-success me-2"></i>GPA Calculation</li>
                <li><i class="bi bi-check-circle text-success me-2"></i>Transcript Download</li>
              </ul>
            </div>
          </div>

          <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="400">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bi bi-calendar-week"></i>
              </div>
              <h4>Class Schedule</h4>
              <p>Access your weekly class schedule with detailed information about times, locations, and instructors for each course.</p>
              <ul class="list-unstyled mt-3">
                <li><i class="bi bi-check-circle text-success me-2"></i>Weekly Schedule</li>
                <li><i class="bi bi-check-circle text-success me-2"></i>Room Assignments</li>
                <li><i class="bi bi-check-circle text-success me-2"></i>Time Management</li>
              </ul>
            </div>
          </div>

          <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="500">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bi bi-shield-check"></i>
              </div>
              <h4>Secure & Private</h4>
              <p>Your data is protected with industry-standard security measures. All information is encrypted and securely stored.</p>
              <ul class="list-unstyled mt-3">
                <li><i class="bi bi-check-circle text-success me-2"></i>Data Encryption</li>
                <li><i class="bi bi-check-circle text-success me-2"></i>Privacy Protection</li>
                <li><i class="bi bi-check-circle text-success me-2"></i>Secure Login</li>
              </ul>
            </div>
          </div>

          <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="600">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bi bi-devices"></i>
              </div>
              <h4>Access Anywhere</h4>
              <p>Access your student portal from any device - desktop, tablet, or mobile. Responsive design ensures optimal experience.</p>
              <ul class="list-unstyled mt-3">
                <li><i class="bi bi-check-circle text-success me-2"></i>Mobile Friendly</li>
                <li><i class="bi bi-check-circle text-success me-2"></i>24/7 Access</li>
                <li><i class="bi bi-check-circle text-success me-2"></i>Cloud-Based</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- About Bicol University Section -->
    <section id="about" class="about-section">
      <div class="container">
        <div class="row">
          <div class="col-lg-12" data-aos="fade-up">
            <div class="about-content">
              <div class="text-center mb-5">
                <h2 class="mb-3">About Bicol University</h2>
                <div class="divider"></div>
              </div>
              
              <div class="row mb-5">
                <div class="col-md-6 mb-4">
                  <h4 class="text-primary mb-3"><i class="bi bi-building me-2"></i>Our History</h4>
                  <p>Bicol University (BU) is a state university in the Philippines with its main campus in Legazpi City, Albay. Established in 1969, BU has grown to become one of the premier educational institutions in the Bicol Region, committed to providing quality education and producing globally competitive graduates.</p>
                </div>
                <div class="col-md-6 mb-4">
                  <h4 class="text-primary mb-3"><i class="bi bi-award me-2"></i>Our Mission</h4>
                  <p>Bicol University is committed to provide quality higher education and advanced studies, research and extension services, and progressive leadership in science and technology, humanities, education, and other fields of study responsive to the needs of the people of the Bicol Region and the nation.</p>
                </div>
              </div>

              <div class="row mb-5">
                <div class="col-md-6 mb-4">
                  <h4 class="text-primary mb-3"><i class="bi bi-eye me-2"></i>Our Vision</h4>
                  <p>Bicol University envisions to be a world-class university producing leaders and change agents for social transformation and development. We aim to be a center of excellence in instruction, research, extension, and production.</p>
                </div>
                <div class="col-md-6 mb-4">
                  <h4 class="text-primary mb-3"><i class="bi bi-star me-2"></i>Core Values</h4>
                  <ul class="list-unstyled">
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Excellence in Education</li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Integrity and Honesty</li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Service to Community</li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Innovation and Research</li>
                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Cultural Heritage</li>
                  </ul>
                </div>
              </div>

              <!-- University Statistics -->
              <div class="row g-4 mt-4">
                <div class="col-md-3 col-6" data-aos="zoom-in" data-aos-delay="100">
                  <div class="stats-card">
                    <div class="stats-number">50+</div>
                    <p class="mb-0">Years of Excellence</p>
                  </div>
                </div>
                <div class="col-md-3 col-6" data-aos="zoom-in" data-aos-delay="200">
                  <div class="stats-card">
                    <div class="stats-number">15,000+</div>
                    <p class="mb-0">Students</p>
                  </div>
                </div>
                <div class="col-md-3 col-6" data-aos="zoom-in" data-aos-delay="300">
                  <div class="stats-card">
                    <div class="stats-number">500+</div>
                    <p class="mb-0">Faculty Members</p>
                  </div>
                </div>
                <div class="col-md-3 col-6" data-aos="zoom-in" data-aos-delay="400">
                  <div class="stats-card">
                    <div class="stats-number">50+</div>
                    <p class="mb-0">Programs Offered</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8 text-center" data-aos="zoom-in">
            <h2 class="display-5 fw-bold mb-4">Ready to Get Started?</h2>
            <p class="lead mb-4">Join thousands of students already using the Bicol University Student Management System</p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
              <a href="signup.php" class="btn btn-light btn-lg px-5">
                <i class="bi bi-person-plus me-2"></i>Create Your Account
              </a>
              <a href="login.php" class="btn btn-outline-light btn-lg px-5">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In Now
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

  </main>

  <!-- Footer -->
  <footer id="footer" class="footer">
    <div class="container">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6">
          <h4>Bicol University</h4>
          <p>Committed to providing quality higher education and producing globally competitive graduates.</p>
          <div class="social-links mt-3">
            <a href="#"><i class="bi bi-twitter"></i></a>
            <a href="#"><i class="bi bi-facebook"></i></a>
            <a href="#"><i class="bi bi-instagram"></i></a>
            <a href="#"><i class="bi bi-linkedin"></i></a>
          </div>
        </div>

        <div class="col-lg-2 col-md-6 footer-links">
          <h4>Quick Links</h4>
          <ul>
            <li><a href="#hero">Home</a></li>
            <li><a href="#features">Features</a></li>
            <li><a href="#about">About BU</a></li>
            <li><a href="login.php">Sign In</a></li>
            <li><a href="signup.php">Sign Up</a></li>
          </ul>
        </div>

        <div class="col-lg-3 col-md-6 footer-links">
          <h4>Student Services</h4>
          <ul>
            <li><a href="login.php">Student Portal</a></li>
            <li><a href="#">Academic Calendar</a></li>
            <li><a href="#">Library Resources</a></li>
            <li><a href="#">Student Support</a></li>
            <li><a href="#">Help Center</a></li>
          </ul>
        </div>

        <div class="col-lg-3 col-md-6 footer-contact">
          <h4>Contact Us</h4>
          <p>
            <strong>Address:</strong><br>
            Rizal Street, Legazpi City<br>
            Albay, Philippines 4500<br><br>
            <strong>Phone:</strong><br>
            (052) 742-1234<br><br>
            <strong>Email:</strong><br>
            info@bicol-u.edu.ph
          </p>
        </div>
      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Bicol University</strong> <span>All Rights Reserved</span></p>
      <div class="credits">
        Student Management System v2.0
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
</body>
</html>

