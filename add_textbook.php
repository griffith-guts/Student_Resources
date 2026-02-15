<?php
session_start();
require_once 'config.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = sanitize($_POST['title'] ?? '');
    $subject  = sanitize($_POST['subject'] ?? '');
    $author   = sanitize($_POST['author'] ?? '');
    $semester = intval($_POST['semester'] ?? 0);
    $contact  = sanitize($_POST['contact'] ?? '');

    if (empty($title) || empty($subject) || empty($author) || empty($contact) || $semester < 1) {
        $error = 'All fields are required.';
    } elseif (!preg_match('/^[0-9]{10}$/', $contact)) {
        $error = 'Contact number must be exactly 10 digits.';
    } elseif ($semester < 1 || $semester > 8) {
        $error = 'Invalid semester.';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("INSERT INTO textbooks (user_id, title, subject, author, semester, contact) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssis", $_SESSION['user_id'], $title, $subject, $author, $semester, $contact);

        if ($stmt->execute()) {
            $success = 'Textbook listed successfully!';
        } else {
            $error = 'Failed to add textbook. Please try again.';
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
    <title>Add Textbook — Student Resource System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Add Textbook</div>
                <div class="topbar-subtitle">List a textbook for other students to find</div>
            </div>
        </div>

        <div class="content-area">
            <div class="card" style="max-width:600px">
                <div class="card-title">📚 Textbook Details</div>
                <div class="card-subtitle">Fill in the details of the textbook you want to share</div>

                <?php if ($error): ?>
                    <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?>
                        <a href="view_textbooks.php" style="color:inherit; margin-left:8px; text-decoration:underline">View all textbooks →</a>
                    </div>
                <?php endif; ?>

                <form method="POST" action="add_textbook.php">
                    <div class="form-group">
                        <label for="title">Book Title</label>
                        <input type="text" id="title" name="title" placeholder="e.g. Data Structures and Algorithms"
                               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" placeholder="e.g. Computer Science"
                                   value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="author">Author</label>
                            <input type="text" id="author" name="author" placeholder="e.g. Thomas H. Cormen"
                                   value="<?= htmlspecialchars($_POST['author'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="semester">Semester</label>
                            <select id="semester" name="semester" required>
                                <option value="">Select Semester</option>
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?= $i ?>" <?= (($_POST['semester'] ?? '') == $i) ? 'selected' : '' ?>>
                                        Semester <?= $i ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="contact">Contact Number</label>
                            <input type="text" id="contact" name="contact" placeholder="10-digit number"
                                   value="<?= htmlspecialchars($_POST['contact'] ?? '') ?>"
                                   maxlength="10" pattern="[0-9]{10}" required>
                        </div>
                    </div>

                    <div style="display:flex; gap:1rem; margin-top:1rem">
                        <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center">
                            📚 Add Textbook
                        </button>
                        <a href="view_textbooks.php" class="btn btn-outline" style="justify-content:center">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
