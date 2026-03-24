<?php
session_start();
require_once 'includes/conn.php';
require_once 'vendor/autoload.php';

use Google\Client;

// 🔐 Google OAuth Config
$client = new Client();
$client->setClientId('949029167238-kg0jqqu1kkk4cpne7hfm197g600c2kou.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/multicampus/callback.php');
$client->addScope(['email', 'profile']);

if (!isset($_GET['code'])) {
    header("Location: index.php");
    exit;
}

try {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (isset($token['error'])) {
        throw new Exception($token['error_description']);
    }

    $client->setAccessToken($token['access_token']);

    $oauth = new Google\Service\Oauth2($client);
    $googleUser = $oauth->userinfo->get();

    $email = $googleUser->email;
    $name = trim($googleUser->name);
    $profile = $googleUser->picture;

    // 🔤 Split name
    $parts = explode(' ', $name);
    $first = array_shift($parts);
    $last = implode(' ', $parts);

    // 🔎 Check existing user
    $stmt = $conn->prepare("SELECT id, role FROM tbl_users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        // 🚦 Redirect based on role
        if ($user['role'] === 'pending') {
            header("Location: choose-role.php");
        } else {
            header("Location: admin/dashboard.php");
        }
        exit;
    }

    // 🆕 FIRST-TIME USER
    $emailLocal = explode('@', $email)[0];
    $defaultPassword = password_hash("mcmY_1946", PASSWORD_DEFAULT);

    if (ctype_digit($emailLocal)) {
        $role = 'student';
        $branch = NULL;
    } else {
        $role = 'pending'; // must choose
        $branch = NULL;
    }

    // 👤 Insert into tbl_users
    $stmt = $conn->prepare(
        "INSERT INTO tbl_users
        (first_name, middle_name, last_name, email, password, role, profile, branch)
        VALUES (?, NULL, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "sssssss",
        $first,
        $last,
        $email,
        $defaultPassword,
        $role,
        $profile,
        $branch
    );
    $stmt->execute();
    $userId = $stmt->insert_id;

    // 🧑‍🎓 Insert student automatically
    if ($role === 'student') {
        $stmt = $conn->prepare(
            "INSERT INTO tbl_students
            (student_id, full_name, department, program, grade_level, course, branch)
            VALUES (?, ?, NULL, NULL, NULL, NULL, NULL)"
        );
        $stmt->bind_param("ss", $emailLocal, $name);
        $stmt->execute();
    }

    // 🔐 Session
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = $role;

    // 🚦 Redirect
    if ($role === 'pending') {
        header("Location: choose-role.php");
    } else {
        header("Location: admin/dashboard.php");
    }
    exit;

} catch (Exception $e) {
    $_SESSION['login_error'] = "Google login failed.";
    header("Location: index.php");
    exit;
}
