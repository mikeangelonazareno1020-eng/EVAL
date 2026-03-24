<?php
session_start();
require_once 'includes/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Email and password are required.";
        header("Location: index.php");
        exit();
    }

    $stmt = $conn->prepare(
        "SELECT id, first_name, middle_name, last_name, email, password 
         FROM tbl_users 
         WHERE email = ?"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {

        $user = $result->fetch_assoc();

        if (md5($password) === $user['password']) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];

            header("Location: admin/dashboard.php");
            exit();

        } else {
            $_SESSION['login_error'] = "Invalid email or password.";
        }

    } else {
        $_SESSION['login_error'] = "Invalid email or password.";
    }

    header("Location: index.php");
    exit();
}
