<?php
// File: login_handler.php
// Path: /ClientBackend/login_handler.php

session_start();
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Client_login.php');
    exit;
}

$user_id = trim($_POST['user_id'] ?? '');
$password = trim($_POST['password'] ?? '');
$role = trim($_POST['role'] ?? ''); // 'teacher' or 'student'

if (empty($user_id) || empty($password) || empty($role)) {
    header('Location: ../Client_login.php?error=empty&role=' . urlencode($role));
    exit;
}

$con = conn();
$md5_pass = md5($password);
$error_url = '../Client_login.php?error=invalid&role=' . urlencode($role);

// ── TEACHER / PROGRAM CHAIR LOGIN ─────────────────────────
if ($role === 'teacher') {
    $stmt = mysqli_prepare(
        $con,
        "SELECT fac_id, full_name, role, department, branch, is_active
         FROM tbl_faculty
         WHERE employee_id = ? AND password = ?
         LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, 'ss', $user_id, $md5_pass);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$user) {
        header('Location: ' . $error_url);
        exit;
    }

    if (!$user['is_active']) {
        header('Location: ../Client_login.php?error=inactive&role=teacher');
        exit;
    }

    // Update last_login
    $upd = mysqli_prepare(
        $con,
        "UPDATE tbl_faculty SET last_login = NOW() WHERE fac_id = ?"
    );
    mysqli_stmt_bind_param($upd, 'i', $user['fac_id']);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    $_SESSION['client_id'] = $user['fac_id'];
    $_SESSION['client_name'] = $user['full_name'];
    $_SESSION['client_role'] = $user['role'];       // teacher | programchair
    $_SESSION['client_type'] = 'faculty';
    $_SESSION['client_department'] = $user['department'];
    $_SESSION['client_branch'] = $user['branch'];

    mysqli_close($con);

    // ── Route by role ──────────────────────────────────────
    if ($user['role'] === 'programchair') {
        header('Location: ../Client/chair_dashboard.php');
    } else {
        header('Location: ../Client/teacher_dashboard.php');
    }
    exit;
}

// ── STUDENT LOGIN ──────────────────────────────────────────
if ($role === 'student') {
    $stmt = mysqli_prepare(
        $con,
        "SELECT stud_id, full_name, department, program, branch, is_active
         FROM tbl_students
         WHERE student_id = ? AND password = ?
         LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, 'ss', $user_id, $md5_pass);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$user) {
        header('Location: ' . $error_url);
        exit;
    }

    if (!$user['is_active']) {
        header('Location: ../Client_login.php?error=inactive&role=student');
        exit;
    }

    // Update last_login
    $upd = mysqli_prepare(
        $con,
        "UPDATE tbl_students SET last_login = NOW() WHERE stud_id = ?"
    );
    mysqli_stmt_bind_param($upd, 'i', $user['stud_id']);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    $_SESSION['client_id'] = $user['stud_id'];
    $_SESSION['client_name'] = $user['full_name'];
    $_SESSION['client_role'] = 'student';
    $_SESSION['client_type'] = 'student';
    $_SESSION['client_department'] = $user['department'];
    $_SESSION['client_branch'] = $user['branch'];

    mysqli_close($con);
    header('Location: ../Client/student_dashboard.php');
    exit;
}

// Fallback
header('Location: ../Client_login.php?error=invalid');
exit;
?>