<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (isset($_SESSION['teacher_id'])) {
    $role = $_SESSION['role'] ?? 'teacher';
    if ($role === 'programchair') {
        header("Location: programchair_portal.php");
        exit();
    }
    header("Location: teacher_portal.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../includes/conn.php';

    $employee_id = trim($_POST['employee_id'] ?? '');
    $password    = trim($_POST['password'] ?? '');

    if (empty($employee_id) || empty($password)) {
        $error = 'Please enter your Employee ID and password.';
    } else {
        $hashed = md5($password);
        $stmt = $conn->prepare("SELECT fac_id, employee_id, full_name, department, program, branch, role FROM tbl_faculty WHERE employee_id = ? AND password = ? AND is_active = 1 LIMIT 1");
        $stmt->bind_param("ss", $employee_id, $hashed);
        $stmt->execute();
        $result = $stmt->get_result();
        $faculty = $result->fetch_assoc();
        $stmt->close();

        if ($faculty) {
            $_SESSION['teacher_id']   = $faculty['fac_id'];
            $_SESSION['employee_id']  = $faculty['employee_id'];
            $_SESSION['teacher_name'] = $faculty['full_name'];
            $_SESSION['department']   = $faculty['department'];
            $_SESSION['program']      = $faculty['program'];
            $_SESSION['branch']       = $faculty['branch'];
            $_SESSION['role']         = $faculty['role'] ?? 'teacher';

            if ($_SESSION['role'] === 'programchair') {
                header("Location: programchair_portal.php");
                exit();
            } elseif ($_SESSION['role'] === 'admin') {
                header("Location: ../index.php");
                exit();
            }
            header("Location: teacher_portal.php");
            exit();
        } else {
            $error = 'Invalid Employee ID or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Login — HCC Evaluation System</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0 }
        :root {
            --navy: #0f2044; --blue: #1a56db; --sky: #e8f0fe;
            --gold: #f59e0b; --white: #ffffff; --muted: #64748b;
            --light: #f8fafc; --border: #e2e8f0; --error: #ef4444
        }
        body { min-height: 100vh; font-family: 'DM Sans', sans-serif; background: var(--light); display: flex; align-items: stretch }
        .left-panel {
            flex: 1; background: var(--navy); display: flex; flex-direction: column;
            justify-content: center; align-items: center; padding: 60px 40px;
            position: relative; overflow: hidden
        }
        .left-panel::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(ellipse 70% 50% at 20% 30%, rgba(26,86,219,.35) 0%, transparent 60%),
                        radial-gradient(ellipse 50% 60% at 80% 80%, rgba(245,158,11,.15) 0%, transparent 55%)
        }
        .left-panel::after {
            content: ''; position: absolute; inset: 0;
            background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 40px 40px
        }
        .left-content { position: relative; z-index: 1; text-align: center; max-width: 380px }
        .school-seal {
            width: 80px; height: 80px;
            background: linear-gradient(135deg, var(--blue), #3b82f6);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 0 0 8px rgba(26,86,219,.2), 0 0 0 16px rgba(26,86,219,.1)
        }
        .school-seal i { font-size: 36px; color: #fff }
        .left-content h1 { font-family: 'DM Serif Display', serif; font-size: 2rem; color: #fff; line-height: 1.2; margin-bottom: 12px }
        .left-content h1 em { color: var(--gold); font-style: italic }
        .left-content p { color: rgba(255,255,255,.6); font-size: .9rem; line-height: 1.6; margin-bottom: 36px }
        .info-pills { display: flex; flex-direction: column; gap: 10px }
        .info-pill {
            display: flex; align-items: center; gap: 10px;
            background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1);
            border-radius: 10px; padding: 10px 16px; text-align: left
        }
        .info-pill i { font-size: 20px; color: var(--gold); flex-shrink: 0 }
        .info-pill span { color: rgba(255,255,255,.75); font-size: .82rem }
        .right-panel { width: 480px; display: flex; flex-direction: column; justify-content: center; padding: 60px 48px; background: var(--white) }
        .login-header { margin-bottom: 36px }
        .eyebrow {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: .75rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase;
            color: var(--blue); background: var(--sky); padding: 4px 10px; border-radius: 20px; margin-bottom: 14px
        }
        .login-header h2 { font-family: 'DM Serif Display', serif; font-size: 1.9rem; color: var(--navy); margin-bottom: 6px }
        .login-header p { color: var(--muted); font-size: .875rem }
        .form-group { margin-bottom: 20px }
        .form-group label { display: block; font-size: .8rem; font-weight: 600; color: var(--navy); margin-bottom: 7px; letter-spacing: .02em }
        .input-wrap { position: relative }
        .input-wrap i.icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 18px; color: var(--muted); pointer-events: none }
        .input-wrap input {
            width: 100%; padding: 12px 14px 12px 42px;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: .9rem; font-family: 'DM Sans', sans-serif;
            color: var(--navy); background: var(--light);
            transition: border-color .2s, box-shadow .2s; outline: none
        }
        .input-wrap input:focus { border-color: var(--blue); background: #fff; box-shadow: 0 0 0 3px rgba(26,86,219,.1) }
        .input-wrap input.has-error { border-color: var(--error) }
        .toggle-pw { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--muted); font-size: 18px; background: none; border: none; padding: 0 }
        .alert-error {
            display: flex; align-items: center; gap: 10px;
            background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px;
            padding: 12px 14px; margin-bottom: 20px; font-size: .85rem; color: #b91c1c;
            animation: slideDown .25s ease
        }
        .alert-error i { font-size: 18px; flex-shrink: 0 }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-8px) } to { opacity: 1; transform: translateY(0) } }
        .btn-login {
            width: 100%; padding: 13px; background: var(--blue); color: #fff;
            border: none; border-radius: 10px; font-size: .95rem; font-weight: 600;
            font-family: 'DM Sans', sans-serif; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: background .2s, transform .1s, box-shadow .2s;
            box-shadow: 0 4px 14px rgba(26,86,219,.3)
        }
        .btn-login:hover { background: #1648c0; box-shadow: 0 6px 18px rgba(26,86,219,.4) }
        .btn-login:active { transform: scale(.98) }
        .login-footer { margin-top: 28px; padding-top: 24px; border-top: 1px solid var(--border); text-align: center; font-size: .8rem; color: var(--muted) }
        .login-footer a { color: var(--blue); text-decoration: none; font-weight: 500 }
        .login-footer a:hover { text-decoration: underline }
        @media(max-width:768px) {
            body { flex-direction: column }
            .left-panel { padding: 40px 24px }
            .left-panel .info-pills { display: none }
            .right-panel { width: 100%; padding: 40px 24px }
        }
    </style>
</head>
<body>

<div class="left-panel">
    <div class="left-content">
        <div class="school-seal"><i class='bx bxs-graduation'></i></div>
        <h1>HCC <em>Faculty</em><br>Evaluation Portal</h1>
        <p>Peer-to-peer evaluation system for Holy Cross College faculty members.</p>
        <div class="info-pills">
            <div class="info-pill"><i class='bx bx-transfer'></i><span>Rate fellow teachers within your department</span></div>
            <div class="info-pill"><i class='bx bx-shield-quarter'></i><span>Responses are confidential and secure</span></div>
            <div class="info-pill"><i class='bx bx-bar-chart-alt-2'></i><span>Track your own evaluation progress</span></div>
        </div>
    </div>
</div>

<div class="right-panel">
    <div class="login-header">
        <div class="eyebrow"><i class='bx bx-user-circle'></i> Faculty Access</div>
        <h2>Welcome back,<br>Educator.</h2>
        <p>Sign in with your Employee ID and password to continue.</p>
    </div>

    <?php if ($error): ?>
        <div class="alert-error">
            <i class='bx bx-error-circle'></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="" autocomplete="off">
        <div class="form-group">
            <label for="employee_id">Employee ID</label>
            <div class="input-wrap">
                <i class='bx bx-id-card icon'></i>
                <input type="text" id="employee_id" name="employee_id"
                    placeholder="e.g. EMP-2026-001"
                    value="<?= htmlspecialchars($_POST['employee_id'] ?? '') ?>"
                    autocomplete="off"
                    class="<?= $error ? 'has-error' : '' ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrap">
                <i class='bx bx-lock-alt icon'></i>
                <input type="password" id="password" name="password"
                    placeholder="Enter your password"
                    autocomplete="new-password"
                    class="<?= $error ? 'has-error' : '' ?>">
                <button type="button" class="toggle-pw" onclick="togglePassword()">
                    <i class='bx bx-hide' id="eyeIcon"></i>
                </button>
            </div>
        </div>
        <button type="submit" class="btn-login">
            <i class='bx bx-log-in-circle'></i> Sign In
        </button>
    </form>

    <div class="login-footer">
        <p>Admin? <a href="../index.php">Go to Admin Login</a></p>
        <p style="margin-top:8px">Forgot your password? Contact your <strong>Program Chair</strong> or <strong>Admin</strong>.</p>
    </div>
</div>

<script>
    // Nuke any autofill on page load
    window.addEventListener('load', function () {
        document.getElementById('password').value = '';
        document.getElementById('employee_id').value = '';
    });

    function togglePassword() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eyeIcon');
        input.type  = input.type === 'password' ? 'text' : 'password';
        icon.className = input.type === 'password' ? 'bx bx-hide' : 'bx bx-show';
    }
</script>
</body>
</html>