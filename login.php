<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'dashboard.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll_number = sanitize(strtoupper($_POST['roll_number'] ?? ''));
    $password    = $_POST['password'] ?? '';

    if (empty($roll_number) || empty($password)) {
        $error = 'Roll number and password are required.';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id, name, password, role, department, semester FROM users WHERE roll_number = ?");
        $stmt->bind_param("s", $roll_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['roll_number']= $roll_number;
                $_SESSION['role']       = $user['role'];
                $_SESSION['department'] = $user['department'];
                $_SESSION['semester']   = $user['semester'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: admin_dashboard.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit;
            } else {
                $error = 'Incorrect password. Please try again.';
            }
        } else {
            $error = 'Roll number not found. Please register first.';
        }

        $stmt->close();
        $conn->close();
    }
}

$unauthorized = isset($_GET['error']) && $_GET['error'] === 'unauthorized';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Student Resource System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-icon">📚</div>
            <div class="auth-logo-text">Student Resource System</div>
        </div>

        <h1 class="auth-title">Welcome Back</h1>
        <p class="auth-subtitle">Sign in with your roll number and password</p>

        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($unauthorized): ?>
            <div class="alert alert-error">⚠️ Access denied. Admin privileges required.</div>
        <?php endif; ?>
        <?php if (isset($_GET['logout'])): ?>
            <div class="alert alert-success">✅ You have been logged out successfully.</div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="roll_number">Roll Number</label>
                <input type="text" id="roll_number" name="roll_number" placeholder="e.g. CS2024001 or ADMIN001"
                       required style="text-transform:uppercase"
                       value="<?= htmlspecialchars($_POST['roll_number'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary form-btn">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                    <polyline points="10 17 15 12 10 7"/>
                    <line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                Sign In
            </button>
        </form>

        <div class="auth-link">
            New student? <a href="register.php">Register here</a>
        </div>
        <div class="auth-link" style="margin-top:0.5rem">
            <a href="index.html" style="color: var(--text-muted); font-size:0.85rem">← Back to Home</a>
        </div>

        
    </div>
</div>
<script>
document.getElementById('roll_number').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>
</body>
</html>
