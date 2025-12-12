<?php
session_start();
require_once('db.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: login.php');
    exit();
}

$studentId = (int)$_SESSION['user_id'];
$statusMsg = '';
$statusType = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Personal Information
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $gender = trim($_POST['gender'] ?? '');
    $nationality = trim($_POST['nationality'] ?? 'Filipino');
    $marital_status = trim($_POST['marital_status'] ?? '');
    $blood_type = trim($_POST['blood_type'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Contact Information
    $contact = trim($_POST['contact'] ?? '');
    
    // Emergency Contact
    $emergency_contact_name = trim($_POST['emergency_contact_name'] ?? '');
    $emergency_contact_relationship = trim($_POST['emergency_contact_relationship'] ?? '');
    $emergency_contact_phone = trim($_POST['emergency_contact_phone'] ?? '');

    // Basic validation
    if ($firstname === '' || $lastname === '') {
        $statusMsg = 'Please fill in First Name and Last Name.';
        $statusType = 'danger';
    } else {
        try {
            // Update students table with all profile information
            $sql = "UPDATE students 
                    SET firstname = ?, lastname = ?, date_of_birth = ?, gender = ?, 
                        nationality = ?, marital_status = ?, blood_type = ?, 
                        address = ?, contact = ?, 
                        emergency_contact_name = ?, emergency_contact_relationship = ?, 
                        emergency_contact_phone = ?
                    WHERE student_id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssssssssssi', 
                $firstname, $lastname, $date_of_birth, $gender,
                $nationality, $marital_status, $blood_type,
                $address, $contact,
                $emergency_contact_name, $emergency_contact_relationship, 
                $emergency_contact_phone, $studentId
            );
            
            if ($stmt->execute()) {
                $statusMsg = 'Profile information saved successfully!';
                $statusType = 'success';
                // Redirect to profile page after 2 seconds
                header("refresh:2;url=profile.php");
            } else {
                throw new Exception("Update failed: " . $stmt->error);
            }
            
            $stmt->close();
        } catch (Exception $e) {
            error_log('Profile update error: ' . $e->getMessage());
            $statusMsg = 'Failed to save profile. Please try again.';
            $statusType = 'danger';
        }
    }
}

// Load current student info
$student = [
    'firstname' => '',
    'lastname' => '',
    'date_of_birth' => '',
    'gender' => '',
    'nationality' => 'Filipino',
    'marital_status' => '',
    'blood_type' => '',
    'address' => '',
    'contact' => '',
    'emergency_contact_name' => '',
    'emergency_contact_relationship' => '',
    'emergency_contact_phone' => ''
];

$sql = "SELECT firstname, lastname, date_of_birth, gender, nationality, marital_status, 
               blood_type, address, contact, emergency_contact_name, 
               emergency_contact_relationship, emergency_contact_phone
        FROM students
        WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $studentId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $student = array_merge($student, $row);
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Complete Your Profile | Bicol University</title>
  <meta name="description" content="Complete Your Student Profile">
  
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
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
  
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 40px 0;
    }
    
    .profile-setup-container {
      max-width: 900px;
      margin: 0 auto;
    }
    
    .profile-setup-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      overflow: hidden;
    }
    
    .profile-setup-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 40px;
      text-align: center;
    }
    
    .profile-setup-header h1 {
      margin: 0;
      font-size: 2rem;
      font-weight: 600;
    }
    
    .profile-setup-header p {
      margin: 10px 0 0;
      opacity: 0.9;
      font-size: 1rem;
    }
    
    .profile-setup-body {
      padding: 40px;
    }
    
    .form-section {
      margin-bottom: 40px;
    }
    
    .form-section-title {
      font-size: 1.3rem;
      font-weight: 600;
      color: #333;
      margin-bottom: 25px;
      padding-bottom: 10px;
      border-bottom: 3px solid #667eea;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .form-section-title i {
      color: #667eea;
    }
    
    .form-label {
      font-weight: 500;
      color: #555;
      margin-bottom: 8px;
    }
    
    .form-control, .form-select {
      border-radius: 8px;
      border: 2px solid #e0e0e0;
      padding: 12px 15px;
      transition: all 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .btn-submit {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      color: white;
      padding: 15px 40px;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 10px;
      transition: all 0.3s;
      width: 100%;
      margin-top: 20px;
    }
    
    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
      color: white;
    }
    
    .btn-back {
      background: #f8f9fa;
      border: 2px solid #dee2e6;
      color: #495057;
      padding: 10px 25px;
      border-radius: 8px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s;
    }
    
    .btn-back:hover {
      background: #e9ecef;
      color: #495057;
    }
    
    .alert {
      border-radius: 10px;
      padding: 15px 20px;
      margin-bottom: 25px;
    }
    
    .progress-indicator {
      display: flex;
      justify-content: space-between;
      margin-bottom: 30px;
      padding: 0 20px;
    }
    
    .progress-step {
      flex: 1;
      text-align: center;
      position: relative;
    }
    
    .progress-step::after {
      content: '';
      position: absolute;
      top: 15px;
      left: 50%;
      width: 100%;
      height: 2px;
      background: #e0e0e0;
      z-index: 0;
    }
    
    .progress-step:last-child::after {
      display: none;
    }
    
    .progress-step.active::after {
      background: #667eea;
    }
    
    .progress-step-number {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background: #e0e0e0;
      color: #999;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      position: relative;
      z-index: 1;
    }
    
    .progress-step.active .progress-step-number {
      background: #667eea;
      color: white;
    }
    
    .progress-step-label {
      margin-top: 8px;
      font-size: 0.85rem;
      color: #999;
    }
    
    .progress-step.active .progress-step-label {
      color: #667eea;
      font-weight: 600;
    }
  </style>
</head>

<body>
  <div class="profile-setup-container">
    <div class="profile-setup-card">
      <!-- Header -->
      <div class="profile-setup-header">
        <h1><i class="bi bi-person-check"></i> Complete Your Profile</h1>
        <p>Please provide your information to complete your student profile</p>
      </div>
      
      <!-- Body -->
      <div class="profile-setup-body">
        <!-- Progress Indicator -->
        <div class="progress-indicator">
          <div class="progress-step active">
            <div class="progress-step-number">1</div>
            <div class="progress-step-label">Personal Info</div>
          </div>
          <div class="progress-step active">
            <div class="progress-step-number">2</div>
            <div class="progress-step-label">Contact</div>
          </div>
          <div class="progress-step active">
            <div class="progress-step-number">3</div>
            <div class="progress-step-label">Emergency</div>
          </div>
        </div>
        
        <?php if ($statusMsg): ?>
          <div class="alert alert-<?php echo $statusType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
            <i class="bi bi-<?php echo $statusType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
            <?php echo htmlspecialchars($statusMsg); ?>
            <?php if ($statusType === 'success'): ?>
              <br><small>Redirecting to your profile...</small>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        
        <form method="POST" action="setprofile.php">
          <!-- Personal Information Section -->
          <div class="form-section">
            <h3 class="form-section-title">
              <i class="bi bi-person-vcard"></i> Personal Information
            </h3>
            
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">First Name <span class="text-danger">*</span></label>
                <input type="text" name="firstname" class="form-control" 
                       value="<?php echo htmlspecialchars($student['firstname']); ?>" required>
              </div>
              
              <div class="col-md-6">
                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="lastname" class="form-control" 
                       value="<?php echo htmlspecialchars($student['lastname']); ?>" required>
              </div>
              
              <div class="col-md-6">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control" 
                       value="<?php echo htmlspecialchars($student['date_of_birth'] ?? ''); ?>">
              </div>
              
              <div class="col-md-6">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                  <option value="">Select Gender</option>
                  <option value="Male" <?php echo $student['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                  <option value="Female" <?php echo $student['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                  <option value="Other" <?php echo $student['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
              </div>
              
              <div class="col-md-6">
                <label class="form-label">Nationality</label>
                <input type="text" name="nationality" class="form-control" 
                       value="<?php echo htmlspecialchars($student['nationality']); ?>" 
                       placeholder="Filipino">
              </div>
              
              <div class="col-md-6">
                <label class="form-label">Marital Status</label>
                <select name="marital_status" class="form-select">
                  <option value="">Select Status</option>
                  <option value="Single" <?php echo $student['marital_status'] === 'Single' ? 'selected' : ''; ?>>Single</option>
                  <option value="Married" <?php echo $student['marital_status'] === 'Married' ? 'selected' : ''; ?>>Married</option>
                  <option value="Divorced" <?php echo $student['marital_status'] === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                  <option value="Widowed" <?php echo $student['marital_status'] === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                </select>
              </div>
              
              <div class="col-md-6">
                <label class="form-label">Blood Type</label>
                <select name="blood_type" class="form-select">
                  <option value="">Select Blood Type</option>
                  <option value="A+" <?php echo $student['blood_type'] === 'A+' ? 'selected' : ''; ?>>A+</option>
                  <option value="A-" <?php echo $student['blood_type'] === 'A-' ? 'selected' : ''; ?>>A-</option>
                  <option value="B+" <?php echo $student['blood_type'] === 'B+' ? 'selected' : ''; ?>>B+</option>
                  <option value="B-" <?php echo $student['blood_type'] === 'B-' ? 'selected' : ''; ?>>B-</option>
                  <option value="AB+" <?php echo $student['blood_type'] === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                  <option value="AB-" <?php echo $student['blood_type'] === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                  <option value="O+" <?php echo $student['blood_type'] === 'O+' ? 'selected' : ''; ?>>O+</option>
                  <option value="O-" <?php echo $student['blood_type'] === 'O-' ? 'selected' : ''; ?>>O-</option>
                </select>
              </div>
              
              <div class="col-12">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="3" 
                          placeholder="Enter your complete address"><?php echo htmlspecialchars($student['address']); ?></textarea>
              </div>
            </div>
          </div>
          
          <!-- Contact Information Section -->
          <div class="form-section">
            <h3 class="form-section-title">
              <i class="bi bi-telephone"></i> Contact Information
            </h3>
            
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Phone Number</label>
                <input type="text" name="contact" class="form-control" 
                       value="<?php echo htmlspecialchars($student['contact']); ?>" 
                       placeholder="+63 912 345 6789">
              </div>
            </div>
          </div>
          
          <!-- Emergency Contact Section -->
          <div class="form-section">
            <h3 class="form-section-title">
              <i class="bi bi-person-exclamation"></i> Emergency Contact
            </h3>
            
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Contact Person</label>
                <input type="text" name="emergency_contact_name" class="form-control" 
                       value="<?php echo htmlspecialchars($student['emergency_contact_name']); ?>" 
                       placeholder="Full Name">
              </div>
              
              <div class="col-md-4">
                <label class="form-label">Relationship</label>
                <select name="emergency_contact_relationship" class="form-select">
                  <option value="">Select Relationship</option>
                  <option value="Father" <?php echo $student['emergency_contact_relationship'] === 'Father' ? 'selected' : ''; ?>>Father</option>
                  <option value="Mother" <?php echo $student['emergency_contact_relationship'] === 'Mother' ? 'selected' : ''; ?>>Mother</option>
                  <option value="Guardian" <?php echo $student['emergency_contact_relationship'] === 'Guardian' ? 'selected' : ''; ?>>Guardian</option>
                  <option value="Spouse" <?php echo $student['emergency_contact_relationship'] === 'Spouse' ? 'selected' : ''; ?>>Spouse</option>
                  <option value="Sibling" <?php echo $student['emergency_contact_relationship'] === 'Sibling' ? 'selected' : ''; ?>>Sibling</option>
                  <option value="Other" <?php echo $student['emergency_contact_relationship'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
              </div>
              
              <div class="col-md-4">
                <label class="form-label">Phone Number</label>
                <input type="text" name="emergency_contact_phone" class="form-control" 
                       value="<?php echo htmlspecialchars($student['emergency_contact_phone']); ?>" 
                       placeholder="+63 923 456 7890">
              </div>
            </div>
          </div>
          
          <!-- Submit Button -->
          <div class="d-flex justify-content-between align-items-center mt-4">
            <a href="profile.php" class="btn-back">
              <i class="bi bi-arrow-left"></i> Back to Profile
            </a>
            <button type="submit" class="btn btn-submit">
              <i class="bi bi-check-circle"></i> Save Profile Information
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
</body>
</html>

