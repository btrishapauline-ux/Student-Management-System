<?php
// REST-like endpoint for student CRUD and search
session_start();
require_once('db.php');

header('Content-Type: application/json');

// Helper to send JSON responses
function json_response($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

// Require admin helper
function require_admin() {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 401);
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === '') {
    json_response(['success' => false, 'message' => 'No action specified'], 400);
}

switch ($action) {
    case 'list':
        list_students($conn);
        break;
    case 'search':
        search_students($conn);
        break;
    case 'view':
        view_student($conn);
        break;
    case 'create':
        require_admin();
        create_student($conn);
        break;
    case 'update':
        require_admin();
        update_student($conn);
        break;
    case 'delete':
        require_admin();
        delete_student($conn);
        break;
    default:
        json_response(['success' => false, 'message' => 'Unknown action'], 400);
}

// ---------- Handlers ----------

function list_students($conn) {
    $sql = "SELECT s.student_id, s.firstname, s.lastname, s.course, s.year_level, 
                   s.email, s.contact, s.address, sl.username, sl.student_email
            FROM students s
            LEFT JOIN student_login sl ON sl.student_id = s.student_id
            ORDER BY s.student_id DESC";
    $result = $conn->query($sql);
    $students = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $students[] = format_student_row($row);
        }
    } else {
        error_log("List students query failed: " . $conn->error);
        json_response(['success' => false, 'message' => 'Query failed: ' . $conn->error], 500);
    }
    json_response(['success' => true, 'data' => $students]);
}

function search_students($conn) {
    $q = trim($_GET['q'] ?? '');
    
    if (empty($q)) {
        json_response(['success' => true, 'data' => []]);
    }
    
    // Comprehensive search across all connected information
    // Start with basic fields that definitely exist, then add optional fields
    $sql = "SELECT DISTINCT s.student_id, s.firstname, s.lastname, s.course, s.year_level, 
                   s.email, s.contact, s.address,
                   sl.username, sl.student_email
            FROM students s
            LEFT JOIN student_login sl ON sl.student_id = s.student_id
            WHERE s.firstname LIKE ? 
               OR s.lastname LIKE ? 
               OR CONCAT(s.firstname, ' ', s.lastname) LIKE ?
               OR s.email LIKE ?
               OR COALESCE(s.contact, '') LIKE ?
               OR COALESCE(s.address, '') LIKE ?
               OR s.course LIKE ?
               OR s.year_level LIKE ?
               OR COALESCE(sl.username, '') LIKE ?
               OR COALESCE(sl.student_email, '') LIKE ?
               OR CAST(s.student_id AS CHAR) LIKE ?
            ORDER BY s.lastname ASC, s.firstname ASC
            LIMIT 100";
    
    $like = '%' . $q . '%';
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Search query prepare failed: " . $conn->error);
        json_response(['success' => false, 'message' => 'Query failed: ' . $conn->error], 500);
    }
    
    // Bind 11 parameters for core search fields
    $stmt->bind_param('sssssssssss', 
        $like, $like, $like, $like, $like, $like, $like, $like, 
        $like, $like, $like
    );
    
    if (!$stmt->execute()) {
        error_log("Search query execute failed: " . $stmt->error);
        $stmt->close();
        json_response(['success' => false, 'message' => 'Search failed: ' . $stmt->error], 500);
    }
    
    $result = $stmt->get_result();
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = format_student_row($row);
    }
    $stmt->close();
    json_response(['success' => true, 'data' => $students]);
}

function view_student($conn) {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        json_response(['success' => false, 'message' => 'Invalid student id'], 400);
    }
    
    // Check if profile_image column exists
    $checkColumnSql = "SHOW COLUMNS FROM students LIKE 'profile_image'";
    $checkResult = $conn->query($checkColumnSql);
    $hasProfileImage = ($checkResult && $checkResult->num_rows > 0);
    
    // Select all fields including profile_image if column exists
    if ($hasProfileImage) {
        $sql = "SELECT s.student_id, s.firstname, s.lastname, s.course, s.year_level, 
                       s.email, s.contact, s.address, s.date_of_birth, s.gender, 
                       s.nationality, s.marital_status, s.blood_type,
                       s.emergency_contact_name, s.emergency_contact_relationship, 
                       s.emergency_contact_phone, s.profile_image,
                       sl.username, sl.student_email
                FROM students s
                LEFT JOIN student_login sl ON sl.student_id = s.student_id
                WHERE s.student_id = ?";
    } else {
        $sql = "SELECT s.student_id, s.firstname, s.lastname, s.course, s.year_level, 
                       s.email, s.contact, s.address, s.date_of_birth, s.gender, 
                       s.nationality, s.marital_status, s.blood_type,
                       s.emergency_contact_name, s.emergency_contact_relationship, 
                       s.emergency_contact_phone,
                       sl.username, sl.student_email
                FROM students s
                LEFT JOIN student_login sl ON sl.student_id = s.student_id
                WHERE s.student_id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        json_response(['success' => false, 'message' => 'Query failed: ' . $conn->error], 500);
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    if (!$row) {
        json_response(['success' => false, 'message' => 'Student not found'], 404);
    }
    json_response(['success' => true, 'data' => format_student_row($row)]);
}

function create_student($conn) {
    $firstname = trim($_POST['firstName'] ?? '');
    $lastname  = trim($_POST['lastName'] ?? '');
    $course    = trim($_POST['course'] ?? '');
    $year      = trim($_POST['yearLevel'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $contact   = trim($_POST['contact'] ?? '');
    $address   = trim($_POST['address'] ?? '');

    if ($firstname === '' || $lastname === '' || $course === '' || $year === '') {
        json_response(['success' => false, 'message' => 'Missing required fields'], 422);
    }

    $sql = "INSERT INTO students (firstname, lastname, course, year_level, email, contact, address, added_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        json_response(['success' => false, 'message' => 'Insert failed'], 500);
    }
    $added_by = $_SESSION['user_id'] ?? null;
    $stmt->bind_param('sssssssi', $firstname, $lastname, $course, $year, $email, $contact, $address, $added_by);
    $ok = $stmt->execute();
    if (!$ok) {
        $stmt->close();
        json_response(['success' => false, 'message' => 'Insert failed: ' . $conn->error], 500);
    }
    $newId = $conn->insert_id;
    $stmt->close();
    json_response(['success' => true, 'data' => ['student_id' => $newId]]);
}

function update_student($conn) {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        json_response(['success' => false, 'message' => 'Invalid student id'], 400);
    }

    // Get all fields
    $firstname = trim($_POST['firstName'] ?? '');
    $lastname  = trim($_POST['lastName'] ?? '');
    $course    = trim($_POST['course'] ?? '');
    $year      = trim($_POST['yearLevel'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $contact   = trim($_POST['contact'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $date_of_birth = trim($_POST['dateOfBirth'] ?? '') ?: null;
    $gender = trim($_POST['gender'] ?? '') ?: null;
    $nationality = trim($_POST['nationality'] ?? '') ?: null;
    $marital_status = trim($_POST['maritalStatus'] ?? '') ?: null;
    $blood_type = trim($_POST['bloodType'] ?? '') ?: null;
    $emergency_contact_name = trim($_POST['emergencyContactName'] ?? '') ?: null;
    $emergency_contact_relationship = trim($_POST['emergencyContactRelationship'] ?? '') ?: null;
    $emergency_contact_phone = trim($_POST['emergencyContactPhone'] ?? '') ?: null;
    $username = trim($_POST['username'] ?? '');
    
    // Handle profile image upload
    $profile_image = null;
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $fileType = $_FILES['profileImage']['type'];
        if (in_array($fileType, $allowedTypes)) {
            $imageData = file_get_contents($_FILES['profileImage']['tmp_name']);
            $profile_image = base64_encode($imageData);
        }
    } elseif (isset($_POST['profileImageBase64']) && !empty($_POST['profileImageBase64'])) {
        // If image is sent as base64 string (from existing image)
        $profile_image = $_POST['profileImageBase64'];
    }

    if ($firstname === '' || $lastname === '' || $course === '' || $year === '') {
        json_response(['success' => false, 'message' => 'Missing required fields'], 422);
    }

    // Check if profile_image column exists
    $checkColumnSql = "SHOW COLUMNS FROM students LIKE 'profile_image'";
    $checkResult = $conn->query($checkColumnSql);
    $hasProfileImage = ($checkResult && $checkResult->num_rows > 0);

    // Build update query
    if ($hasProfileImage && $profile_image !== null) {
        $sql = "UPDATE students 
                SET firstname = ?, lastname = ?, course = ?, year_level = ?, 
                    email = ?, contact = ?, address = ?, date_of_birth = ?, 
                    gender = ?, nationality = ?, marital_status = ?, blood_type = ?,
                    emergency_contact_name = ?, emergency_contact_relationship = ?, 
                    emergency_contact_phone = ?, profile_image = ?
                WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            json_response(['success' => false, 'message' => 'Update failed: ' . $conn->error], 500);
        }
        $stmt->bind_param('ssssssssssssssssi', 
            $firstname, $lastname, $course, $year, $email, $contact, $address,
            $date_of_birth, $gender, $nationality, $marital_status, $blood_type,
            $emergency_contact_name, $emergency_contact_relationship, 
            $emergency_contact_phone, $profile_image, $id
        );
    } else {
        $sql = "UPDATE students 
                SET firstname = ?, lastname = ?, course = ?, year_level = ?, 
                    email = ?, contact = ?, address = ?, date_of_birth = ?, 
                    gender = ?, nationality = ?, marital_status = ?, blood_type = ?,
                    emergency_contact_name = ?, emergency_contact_relationship = ?, 
                    emergency_contact_phone = ?
                WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            json_response(['success' => false, 'message' => 'Update failed: ' . $conn->error], 500);
        }
        $stmt->bind_param('ssssssssssssssi', 
            $firstname, $lastname, $course, $year, $email, $contact, $address,
            $date_of_birth, $gender, $nationality, $marital_status, $blood_type,
            $emergency_contact_name, $emergency_contact_relationship, 
            $emergency_contact_phone, $id
        );
    }
    
    $ok = $stmt->execute();
    if (!$ok) {
        $stmt->close();
        json_response(['success' => false, 'message' => 'Update failed: ' . $stmt->error], 500);
    }
    $stmt->close();
    
    // Update student_login table if username or email changed
    if ($username !== '' || $email !== '') {
        $sqlLogin = "UPDATE student_login 
                     SET username = ?, student_email = ?, course = ?, year_level = ?
                     WHERE student_id = ?";
        $stmtLogin = $conn->prepare($sqlLogin);
        if ($stmtLogin) {
            $stmtLogin->bind_param('ssssi', $username, $email, $course, $year, $id);
            $stmtLogin->execute();
            $stmtLogin->close();
        }
    }
    
    json_response(['success' => true]);
}

function delete_student($conn) {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        json_response(['success' => false, 'message' => 'Invalid student id'], 400);
    }
    $sql = "DELETE FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        json_response(['success' => false, 'message' => 'Delete failed'], 500);
    }
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    $stmt->close();
    if (!$ok) {
        json_response(['success' => false, 'message' => 'Delete failed: ' . $conn->error], 500);
    }
    json_response(['success' => true]);
}

function format_student_row($row) {
    return [
        'id' => (int)$row['student_id'],
        'studentId' => (int)$row['student_id'],
        'firstName' => $row['firstname'] ?? '',
        'lastName' => $row['lastname'] ?? '',
        'username' => $row['username'] ?? '',
        'email' => $row['email'] ?? $row['student_email'] ?? '',
        'program' => $row['course'] ?? '',
        'course' => $row['course'] ?? '',
        'yearLevel' => $row['year_level'] ?? '',
        'year_level' => $row['year_level'] ?? '',
        'contact' => $row['contact'] ?? '',
        'address' => $row['address'] ?? '',
        'dateOfBirth' => $row['date_of_birth'] ?? '',
        'date_of_birth' => $row['date_of_birth'] ?? '',
        'gender' => $row['gender'] ?? '',
        'nationality' => $row['nationality'] ?? '',
        'maritalStatus' => $row['marital_status'] ?? '',
        'marital_status' => $row['marital_status'] ?? '',
        'bloodType' => $row['blood_type'] ?? '',
        'blood_type' => $row['blood_type'] ?? '',
        'emergencyContactName' => $row['emergency_contact_name'] ?? '',
        'emergency_contact_name' => $row['emergency_contact_name'] ?? '',
        'emergencyContactRelationship' => $row['emergency_contact_relationship'] ?? '',
        'emergency_contact_relationship' => $row['emergency_contact_relationship'] ?? '',
        'emergencyContactPhone' => $row['emergency_contact_phone'] ?? '',
        'emergency_contact_phone' => $row['emergency_contact_phone'] ?? '',
        'profileImage' => $row['profile_image'] ?? '',
        'profile_image' => $row['profile_image'] ?? '',
        // UI-only fields for compatibility
        'isActive' => true
    ];
}

