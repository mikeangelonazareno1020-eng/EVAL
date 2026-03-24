<?php
session_start();
require_once 'includes/conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_POST['role'];
$programId = $_POST['program_id'];
$branch = $_POST['branch'];

// 🔄 Update tbl_users
$stmt = $conn->prepare(
    "UPDATE tbl_users 
     SET role = ?, branch = ? 
     WHERE id = ?"
);
$stmt->bind_param("ssi", $role, $branch, $userId);
$stmt->execute();

// 📘 Get program details
$stmt = $conn->prepare(
    "SELECT program_name, department 
     FROM programs 
     WHERE id = ?"
);
$stmt->bind_param("i", $programId);
$stmt->execute();
$program = $stmt->get_result()->fetch_assoc();

// 👨‍🏫 If Faculty → insert to tbl_faculty
if ($role === 'faculty') {

    $stmt = $conn->prepare(
        "INSERT INTO tbl_faculty
        (employee_id, full_name, department, program, grade_level, course, branch)
        SELECT 
            email,
            CONCAT(first_name, ' ', last_name),
            ?,
            ?,
            NULL,
            NULL,
            ?
        FROM tbl_users
        WHERE id = ?"
    );

    $stmt->bind_param(
        "sssi",
        $program['department'],
        $program['program_name'],
        $branch,
        $userId
    );
    $stmt->execute();
}

// 🔐 Update session
$_SESSION['role'] = $role;

header("Location: admin/dashboard.php");
exit;
