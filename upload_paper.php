<?php
session_start();
require_once 'config.php';
requireLogin();

// Ensure uploads directory exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = sanitize($_POST['subject'] ?? '');
    $year    = intval($_POST['year'] ?? 0);

    if (empty($subject) || $year < 2000 || $year > (int)date('Y')) {
        $error = 'Please provide a valid subject and year.';
    } elseif (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a PDF file to upload.';
    } else {
        $file     = $_FILES['pdf_file'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $file_size = $file['size'];

        // Validate: PDF only, max 10MB
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($file_ext !== 'pdf' || $mime !== 'application/pdf') {
            $error = 'Only PDF files are allowed.';
        } elseif ($file_size > 10 * 1024 * 1024) {
            $error = 'File size must not exceed 10MB.';
        } else {
            // Generate safe file name
            $safe_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $subject) . '_' . $year . '_' . time() . '.pdf';
            $dest_path = UPLOAD_DIR . $safe_name;

            if (move_uploaded_file($file['tmp_name'], $dest_path)) {
                $conn = getConnection();
                $stmt = $conn->prepare("INSERT INTO question_papers (user_id, subject, year, file_path) VALUES (?, ?, ?, ?)");
                $file_path_db = UPLOAD_URL . $safe_name;
                $stmt->bind_param("isis", $_SESSION['user_id'], $subject, $year, $file_path_db);

                if ($stmt->execute()) {
                    $success = 'Question paper uploaded successfully!';
                } else {
                    $error = 'Database error. Please try again.';
                    unlink($dest_path);
                }
                $stmt->close();
                $conn->close();
            } else {
                $error = 'Failed to save file. Check permissions on /uploads folder.';
            }
        }
    }
}

// Current year for the year dropdown
$current_year = (int)date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Question Paper — Student Resource System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Upload Question Paper</div>
                <div class="topbar-subtitle">Share past year papers to help fellow students</div>
            </div>
        </div>

        <div class="content-area">
            <div class="card" style="max-width:600px">
                <div class="card-title">📄 Paper Details</div>
                <div class="card-subtitle">Upload PDF files only — maximum 10MB</div>

                <?php if ($error): ?>
                    <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?>
                        <a href="view_papers.php" style="color:inherit; margin-left:8px; text-decoration:underline">View all papers →</a>
                    </div>
                <?php endif; ?>

                <form method="POST" action="upload_paper.php" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" placeholder="e.g. Data Structures"
                                   value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="year">Exam Year</label>
                            <select id="year" name="year" required>
                                <option value="">Select Year</option>
                                <?php for ($y = $current_year; $y >= 2010; $y--): ?>
                                    <option value="<?= $y ?>" <?= (($_POST['year'] ?? '') == $y) ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>PDF File</label>
                        <div class="file-drop" id="fileDrop">
                            <span class="file-drop-icon">📁</span>
                            <div id="fileDropText">
                                <strong style="color:var(--text-light)">Click to choose or drag & drop a PDF</strong>
                                <p style="color:var(--text-muted); font-size:0.85rem; margin-top:4px">Max file size: 10MB</p>
                            </div>
                            <input type="file" name="pdf_file" id="pdfInput" accept=".pdf" required>
                        </div>
                    </div>

                    <div style="display:flex; gap:1rem; margin-top:1rem">
                        <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center">
                            📤 Upload Paper
                        </button>
                        <a href="view_papers.php" class="btn btn-outline" style="justify-content:center">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const input = document.getElementById('pdfInput');
const dropText = document.getElementById('fileDropText');
const dropZone = document.getElementById('fileDrop');

input.addEventListener('change', function() {
    if (this.files.length > 0) {
        const file = this.files[0];
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        dropText.innerHTML = `<strong style="color:var(--amber)">${file.name}</strong>
            <p style="color:var(--text-muted); font-size:0.85rem; margin-top:4px">Size: ${sizeMB} MB</p>`;
    }
});

// Drag and drop events
dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    if (e.dataTransfer.files.length) {
        input.files = e.dataTransfer.files;
        input.dispatchEvent(new Event('change'));
    }
});
</script>
</body>
</html>
