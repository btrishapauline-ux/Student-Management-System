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
    $sql = "SELECT student_id, firstname, lastname, course, year_level, email, contact, address 
            FROM students ORDER BY student_id DESC";
    $result = $conn->query($sql);
    $students = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $students[] = format_student_row($row);
        }
    }
    json_response(['success' => true, 'data' => $students]);
}

function search_students($conn) {
    $q = trim($_GET['q'] ?? '');
    $sql = "SELECT student_id, firstname, lastname, course, year_level, email, contact, address
            FROM students
            WHERE firstname LIKE ? OR lastname LIKE ? OR CONCAT(firstname,' ',lastname) LIKE ?
               OR course LIKE ? OR email LIKE ?
            ORDER BY lastname ASC, firstname ASC";
    $like = '%' . $q . '%';
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        json_response(['success' => false, 'message' => 'Query failed'], 500);
    }
    $stmt->bind_param('sssss', $like, $like, $like, $like, $like);
    $stmt->execute();
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
    $sql = "SELECT student_id, firstname, lastname, course, year_level, email, contact, address
            FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        json_response(['success' => false, 'message' => 'Query failed'], 500);
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

    $sql = "UPDATE students 
            SET firstname = ?, lastname = ?, course = ?, year_level = ?, email = ?, contact = ?, address = ?
            WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        json_response(['success' => false, 'message' => 'Update failed'], 500);
    }
    $stmt->bind_param('sssssssi', $firstname, $lastname, $course, $year, $email, $contact, $address, $id);
    $ok = $stmt->execute();
    $stmt->close();
    if (!$ok) {
        json_response(['success' => false, 'message' => 'Update failed: ' . $conn->error], 500);
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
        'firstName' => $row['firstname'],
        'lastName' => $row['lastname'],
        'studentId' => $row['student_id'],
        'email' => $row['email'] ?? '',
        'program' => $row['course'],
        'yearLevel' => $row['year_level'],
        'contact' => $row['contact'] ?? '',
        'address' => $row['address'] ?? '',
        // UI-only fields for compatibility
        'isActive' => true,
        'dateOfBirth' => '',
        'gender' => ''
    ];
}

