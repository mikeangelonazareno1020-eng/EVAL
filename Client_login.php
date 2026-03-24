<?php
// File: Client_login.php
// Path: /Client_login.php

session_start();

// If already logged in, redirect to appropriate dashboard
if (isset($_SESSION['client_type'])) {
    if ($_SESSION['client_type'] === 'faculty') {
        header('Location: Client/teacher_dashboard.php');
    } else {
        header('Location: Client/student_dashboard.php');
    }
    exit;
}

$error = $_GET['error'] ?? '';
$role = $_GET['role'] ?? 'teacher';

$error_messages = [
    'empty' => 'Please fill in all fields.',
    'invalid' => 'Invalid ID or password. Please try again.',
    'inactive' => 'Your account has been deactivated. Contact your administrator.',
];
$error_text = $error_messages[$error] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holy Cross College — Client Login</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --navy: #0a1f3c;
            --deep-blue: #0d2d5e;
            --mid-blue: #1a4a8a;
            --sky: #4a90d9;
            --pale-sky: #a8c8f0;
            --ice: #ddeeff;
            --frost: #eef5ff;
            --white: #ffffff;
            --gold: #c8a96e;
            --text-dark: #0d1f35;
            --text-mid: #3a5278;
            --text-light: #6a8aaa;
            --error: #e05c5c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(160deg, var(--navy) 0%, var(--deep-blue) 40%, var(--mid-blue) 75%, #1e6ab0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            padding: 2rem;
        }

        /* Background decorations */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 110%, rgba(74, 144, 217, 0.18) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% -10%, rgba(168, 200, 240, 0.12) 0%, transparent 55%);
            pointer-events: none;
        }

        /* CSS Cross watermark */
        .bg-cross {
            position: fixed;
            right: 6%;
            top: 50%;
            transform: translateY(-50%);
            width: 340px;
            height: 340px;
            opacity: 0.04;
            pointer-events: none;
        }

        .bg-cross::before,
        .bg-cross::after {
            content: '';
            position: absolute;
            background: var(--white);
            border-radius: 4px;
        }

        .bg-cross::before {
            width: 55px;
            height: 100%;
            left: 50%;
            transform: translateX(-50%);
        }

        .bg-cross::after {
            height: 55px;
            width: 100%;
            top: 30%;
            transform: translateY(-50%);
        }

        /* Rings */
        .ring {
            position: fixed;
            border-radius: 50%;
            border: 1px solid rgba(168, 200, 240, 0.07);
            animation: expand 9s ease-out infinite;
            pointer-events: none;
        }

        .ring:nth-child(1) {
            width: 420px;
            height: 420px;
            left: -140px;
            bottom: -140px;
            animation-delay: 0s;
        }

        .ring:nth-child(2) {
            width: 640px;
            height: 640px;
            left: -240px;
            bottom: -240px;
            animation-delay: 3s;
        }

        .ring:nth-child(3) {
            width: 860px;
            height: 860px;
            left: -340px;
            bottom: -340px;
            animation-delay: 6s;
        }

        @keyframes expand {
            0% {
                opacity: 0.15;
                transform: scale(0.95);
            }

            100% {
                opacity: 0;
                transform: scale(1.1);
            }
        }

        /* ── CARD ── */
        .login-card {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.97);
            border-radius: 12px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 40px 80px rgba(5, 15, 35, 0.45), 0 0 0 1px rgba(168, 200, 240, 0.15);
            overflow: hidden;
            animation: cardUp 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes cardUp {
            from {
                opacity: 0;
                transform: translateY(32px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Card header */
        .card-header {
            background: linear-gradient(135deg, var(--navy) 0%, var(--mid-blue) 100%);
            padding: 2.4rem 2.4rem 2rem;
            text-align: center;
            position: relative;
        }

        /* Small cross in header */
        .header-cross {
            width: 28px;
            height: 34px;
            margin: 0 auto 1rem;
            position: relative;
        }

        .header-cross::before,
        .header-cross::after {
            content: '';
            position: absolute;
            background: var(--gold);
            border-radius: 2px;
        }

        .header-cross::before {
            width: 5px;
            height: 100%;
            left: 50%;
            transform: translateX(-50%);
        }

        .header-cross::after {
            height: 5px;
            width: 100%;
            top: 28%;
            transform: translateY(-50%);
        }

        .card-header h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.7rem;
            font-weight: 300;
            color: var(--white);
            letter-spacing: 0.02em;
            line-height: 1.2;
        }

        .card-header h1 span {
            font-style: italic;
            color: var(--pale-sky);
        }

        .card-header p {
            font-size: 0.75rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(168, 200, 240, 0.6);
            margin-top: 6px;
        }

        /* Gold divider */
        .gold-line {
            width: 50px;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            margin: 0.9rem auto 0;
        }

        /* ── ROLE TABS ── */
        .role-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 1px solid var(--ice);
        }

        .tab-btn {
            padding: 1rem;
            background: var(--frost);
            border: none;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-light);
            cursor: pointer;
            transition: all 0.25s ease;
            border-bottom: 2px solid transparent;
        }

        .tab-btn:first-child {
            border-right: 1px solid var(--ice);
        }

        .tab-btn.active {
            background: var(--white);
            color: var(--mid-blue);
            border-bottom-color: var(--mid-blue);
        }

        /* ── FORM BODY ── */
        .card-body {
            padding: 2rem 2.4rem 2.4rem;
        }

        .form-panel {
            display: none;
        }

        .form-panel.active {
            display: block;
            animation: fadeIn 0.3s ease both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-intro {
            font-size: 0.82rem;
            font-weight: 300;
            color: var(--text-light);
            margin-bottom: 1.6rem;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 1.1rem;
        }

        .form-label {
            display: block;
            font-size: 0.7rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--text-mid);
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            background: var(--frost);
            border: 1px solid var(--ice);
            border-radius: 4px;
            padding: 12px 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.92rem;
            font-weight: 300;
            color: var(--text-dark);
            outline: none;
            transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
        }

        .form-input:focus {
            background: var(--white);
            border-color: var(--sky);
            box-shadow: 0 0 0 3px rgba(74, 144, 217, 0.12);
        }

        .form-input::placeholder {
            color: var(--text-light);
            font-weight: 300;
        }

        /* Error message */
        .error-box {
            background: rgba(224, 92, 92, 0.08);
            border: 1px solid rgba(224, 92, 92, 0.25);
            border-radius: 4px;
            padding: 10px 14px;
            font-size: 0.82rem;
            color: var(--error);
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .error-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--error);
            flex-shrink: 0;
        }

        /* Submit button */
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--sky), var(--mid-blue));
            color: var(--white);
            border: none;
            border-radius: 4px;
            padding: 13px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            cursor: pointer;
            margin-top: 1.4rem;
            transition: all 0.3s ease;
            box-shadow: 0 6px 24px rgba(26, 74, 138, 0.35);
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 32px rgba(74, 144, 217, 0.45);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Footer of card */
        .card-footer {
            text-align: center;
            padding: 1.2rem 2.4rem 1.6rem;
            border-top: 1px solid var(--ice);
        }

        .card-footer a {
            font-size: 0.78rem;
            color: var(--text-light);
            text-decoration: none;
            letter-spacing: 0.04em;
            transition: color 0.2s;
        }

        .card-footer a:hover {
            color: var(--mid-blue);
        }

        .card-footer .sep {
            margin: 0 8px;
            color: var(--ice);
        }

        /* Back to home */
        .back-link {
            position: fixed;
            top: 1.5rem;
            left: 1.8rem;
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(168, 200, 240, 0.55);
            font-size: 0.75rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            text-decoration: none;
            transition: color 0.2s ease;
            z-index: 20;
        }

        .back-link:hover {
            color: rgba(168, 200, 240, 0.9);
        }

        .back-arrow {
            width: 20px;
            height: 1px;
            background: currentColor;
            position: relative;
        }

        .back-arrow::before {
            content: '';
            position: absolute;
            left: 0;
            top: -3px;
            width: 7px;
            height: 7px;
            border-left: 1px solid currentColor;
            border-bottom: 1px solid currentColor;
            transform: rotate(45deg);
        }
    </style>
</head>

<body>

    <div class="ring"></div>
    <div class="ring"></div>
    <div class="ring"></div>
    <div class="bg-cross"></div>

    <a href="index.php" class="back-link">
        <span class="back-arrow"></span>
        Back to Home
    </a>

    <div class="login-card">

        <!-- Header -->
        <div class="card-header">
            <!-- <div class="header-cross"></div> -->
            <h1>Holy Cross <span>College</span></h1>
            <p>Client Portal</p>
            <div class="gold-line"></div>
        </div>

        <!-- Role tabs -->
        <div class="role-tabs">
            <button class="tab-btn <?= ($role !== 'student') ? 'active' : '' ?>" onclick="switchTab('teacher')"
                id="tab-teacher">
                Teacher / Faculty
            </button>
            <button class="tab-btn <?= ($role === 'student') ? 'active' : '' ?>" onclick="switchTab('student')"
                id="tab-student">
                Student
            </button>
        </div>

        <!-- Form body -->
        <div class="card-body">

            <?php if ($error_text): ?>
                <div class="error-box">
                    <div class="error-dot"></div>
                    <?= htmlspecialchars($error_text) ?>
                </div>
            <?php endif; ?>

            <!-- Teacher / Faculty form -->
            <div class="form-panel <?= ($role !== 'student') ? 'active' : '' ?>" id="panel-teacher">
                <p class="form-intro">Sign in using your Employee ID and assigned password.</p>
                <form method="POST" action="ClientBackend/login_handler.php">
                    <input type="hidden" name="role" value="teacher">
                    <div class="form-group">
                        <label class="form-label" for="teacher-id">Employee ID</label>
                        <input class="form-input" type="text" id="teacher-id" name="user_id"
                            placeholder="e.g. EMP-2026-001" required
                            value="<?= ($role !== 'student') ? htmlspecialchars($_POST['user_id'] ?? '') : '' ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="teacher-pass">Password</label>
                        <input class="form-input" type="password" id="teacher-pass" name="password"
                            placeholder="Enter your password" required>
                    </div>
                    <button class="btn-submit" type="submit">Sign In to Faculty Portal</button>
                </form>
            </div>

            <!-- Student form -->
            <div class="form-panel <?= ($role === 'student') ? 'active' : '' ?>" id="panel-student">
                <p class="form-intro">Sign in using your Student ID and assigned password.</p>
                <form method="POST" action="ClientBackend/login_handler.php">
                    <input type="hidden" name="role" value="student">
                    <div class="form-group">
                        <label class="form-label" for="student-id">Student ID</label>
                        <input class="form-input" type="text" id="student-id" name="user_id"
                            placeholder="e.g. STU-2026-001" required
                            value="<?= ($role === 'student') ? htmlspecialchars($_POST['user_id'] ?? '') : '' ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="student-pass">Password</label>
                        <input class="form-input" type="password" id="student-pass" name="password"
                            placeholder="Enter your password" required>
                    </div>
                    <button class="btn-submit" type="submit">Sign In to Student Portal</button>
                </form>
            </div>

        </div><!-- /card-body -->

        <!-- Footer links -->
        <div class="card-footer">
            <a href="mailto:ict@holycross.edu.ph">Forgot Password?</a>
            <span class="sep">|</span>
            <a href="index.php">Back to Home</a>
        </div>

    </div><!-- /login-card -->

    <script>
        function switchTab(role) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.form-panel').forEach(p => p.classList.remove('active'));
            document.getElementById('tab-' + role).classList.add('active');
            document.getElementById('panel-' + role).classList.add('active');
        }

        // Pre-select tab based on PHP $role
        const preRole = '<?= $role ?>';
        if (preRole === 'student') switchTab('student');
    </script>
</body>

</html>