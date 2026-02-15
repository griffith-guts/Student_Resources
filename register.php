<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = sanitize($_POST['name'] ?? '');
    $roll_number = sanitize(strtoupper($_POST['roll_number'] ?? ''));
    $department  = sanitize($_POST['department'] ?? '');
    $semester    = intval($_POST['semester'] ?? 0);
    $password    = $_POST['password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name) || empty($roll_number) || empty($department) || empty($semester) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (strlen($name) < 3) {
        $error = 'Full name must be at least 3 characters.';
    } elseif (!preg_match('/^[A-Z0-9\-]+$/', $roll_number)) {
        $error = 'Roll number can only contain letters, numbers, and hyphens.';
    } elseif ($semester < 1 || $semester > 8) {
        $error = 'Semester must be between 1 and 8.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $conn = getConnection();

        // Check if roll number already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE roll_number = ?");
        $stmt->bind_param("s", $roll_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Roll number already registered. Please login.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'student';

            $insert = $conn->prepare("INSERT INTO users (name, roll_number, department, semester, password, role) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->bind_param("sssiss", $name, $roll_number, $department, $semester, $hashed_password, $role);

            if ($insert->execute()) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $insert->close();
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Student Resource System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-icon">📚</div>
            <div class="auth-logo-text">Student Resource System</div>
        </div>

        <h1 class="auth-title">Create Account</h1>
        <p class="auth-subtitle">Join the platform to share and access resources</p>

        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php" novalidate>
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="e.g. Rahul Sharma" required
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="roll_number">Roll Number</label>
                    <input type="text" id="roll_number" name="roll_number" placeholder="e.g. CS2024001" required
                           value="<?= htmlspecialchars($_POST['roll_number'] ?? '') ?>" style="text-transform:uppercase">
                </div>
                <div class="form-group">
                    <label for="semester">Semester</label>
                    <select id="semester" name="semester" required>
                        <option value="">Select</option>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i ?>" <?= (($_POST['semester'] ?? '') == $i) ? 'selected' : '' ?>>
                                Semester <?= $i ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="department">Department</label>
                <select id="department" name="department" required>
                    <option value="">Select Department</option>
                    <?php
                    $depts = ['Computer Science', 'Information Technology', 'Electronics & Communication',
                              'Mechanical Engineering', 'Civil Engineering', 'Electrical Engineering',
                              'Chemical Engineering', 'Biotechnology', 'Mathematics', 'Physics', 'Other'];
                    foreach ($depts as $dept):
                        $sel = (($_POST['department'] ?? '') === $dept) ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars($dept) ?>" <?= $sel ?>><?= htmlspecialchars($dept) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Min. 6 characters" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary form-btn">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                Create Account
            </button>
        </form>

        <div class="auth-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
        <div class="auth-link" style="margin-top:0.5rem">
            <a href="index.html" style="color: var(--text-muted); font-size:0.85rem">← Back to Home</a>
        </div>
    </div>
</div>

<script>
// Client-side password match check
document.querySelector('form').addEventListener('submit', function(e) {
    const pw  = document.getElementById('password').value;
    const cpw = document.getElementById('confirm_password').value;
    if (pw !== cpw) {
        e.preventDefault();
        alert('Passwords do not match!');
    }
});
// Auto-uppercase roll number
document.getElementById('roll_number').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>
</body>
</html>
