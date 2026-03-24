<?php
session_start();
require_once 'includes/conn.php';

// Google OAuth configuration
$google_client_id = '949029167238-kg0jqqu1kkk4cpne7hfm197g600c2kou.apps.googleusercontent.com';
$google_redirect_uri = 'http://localhost/multicampus/callback.php';

// Check if user is already logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? '';
?>

<?php include 'includes/header.php'; ?>

<!-- LOADING OVERLAY -->
<div id="loadingOverlay" style="display:none;">
    <div class="loader"></div>
</div>

<div class="container">
    <div class="logo">
        <h1>Holy Cross Colleges Philippines</h1>
        <p style="color: #666; font-size: 14px;">Welcome</p>
    </div>


    <form method="POST" action="login.php" onsubmit="showLoader()">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="login-btn">Sign In</button>
    </form>

    <div class="divider">
        <span>or</span>
    </div>

    <a href="https://accounts.google.com/o/oauth2/v2/auth?
client_id=<?php echo urlencode($google_client_id); ?>
&redirect_uri=<?php echo urlencode($google_redirect_uri); ?>
&response_type=code
&scope=openid%20email%20profile
&access_type=offline
&prompt=select_account" class="google-btn" onclick="showLoader()">
        <svg class="google-icon" viewBox="0 0 24 24">
            <path fill="#EA4335"
                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
            <path fill="#34A853"
                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
            <path fill="#FBBC05"
                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
            <path fill="#4285F4"
                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
        </svg>
        Continue with Google
    </a>
</div>

<script>
    function showLoader() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    // LOGOUT CONFIRMATION
    function confirmLogout() {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You will be logged out.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#2c3e50',
            confirmButtonText: 'Yes, Logout'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoader();
                setTimeout(() => {
                    window.location.href = 'logout.php';
                }, 3000);
            }
        });
    }
</script>

<?php if (isset($_SESSION['login_error'])): ?>
    <script>
        showLoader();
        setTimeout(() => {
            document.getElementById('loadingOverlay').style.display = 'none';

            Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                text: '<?php echo $_SESSION['login_error']; ?>',
                confirmButtonColor: '#2c3e50'
            });
        }, 3000);
    </script>
    <?php unset($_SESSION['login_error']); endif; ?>

<?php include 'includes/footer.php'; ?>