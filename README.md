# 📚 Student Resource Sharing System
## Complete Setup Guide for XAMPP (localhost)

---

## 📁 Folder Structure

```
student_resource_system/
│
├── index.html              ← Home page
├── register.php            ← Student registration
├── login.php               ← Login (students & admin)
├── logout.php              ← Session destroyer
│
├── dashboard.php           ← Student dashboard
├── sidebar.php             ← Shared sidebar (included by all pages)
├── add_textbook.php        ← Add textbook listing
├── view_textbooks.php      ← Browse textbooks with filters
├── upload_paper.php        ← Upload PDF question papers
├── view_papers.php         ← Download question papers
├── planner.php             ← Study planner (JS-powered)
├── videos.php              ← View YouTube resources
│
├── admin_dashboard.php     ← Admin overview
├── admin_users.php         ← View & delete students
├── admin_textbooks.php     ← Approve/delete textbooks
├── admin_videos.php        ← Add/remove YouTube videos
│
├── config.php              ← DB connection & helpers
├── style.css               ← Main stylesheet
├── database.sql            ← SQL schema + admin seed
│
└── uploads/                ← PDF files stored here
```

---

## ⚙️ XAMPP Setup Steps

### Step 1: Copy the Project Folder
Copy the entire `student_resource_system` folder to:
```
C:\xampp\htdocs\student_resource_system\
```

### Step 2: Start XAMPP Services
- Open **XAMPP Control Panel**
- Start **Apache**
- Start **MySQL**

### Step 3: Create the Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **"New"** in the left panel
3. Name it: `student_resource_db`  → Click **Create**
4. Click on `student_resource_db` in the left panel
5. Click the **SQL** tab at the top
6. Open the `database.sql` file and **paste its entire content**
7. Click **Go** to execute

### Step 4: Configure DB Connection (if needed)
Open `config.php` and verify:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // Your MySQL username
define('DB_PASS', '');         // Your MySQL password (blank by default in XAMPP)
define('DB_NAME', 'student_resource_db');
```

### Step 5: Create Uploads Folder
Make sure the `uploads` folder exists inside the project:
```
C:\xampp\htdocs\student_resource_system\uploads\
```
If it doesn't exist, create it manually.

**On Windows**, right-click → New Folder → name it `uploads`

### Step 6: Open in Browser
Go to: `http://localhost/student_resource_system/`

---

## 🔑 Login Credentials

### Admin Account (pre-seeded)
| Field       | Value       |
|------------|-------------|
| Roll Number | `ADMIN001`  |
| Password    | `Admin@123` |

### Student Account
Register a new student from the Register page.

---

## 🗄️ Database Tables

| Table            | Purpose                              |
|-----------------|--------------------------------------|
| `users`          | All users (students + admin)         |
| `textbooks`      | Textbook listings                    |
| `question_papers`| Uploaded PDF question papers         |
| `videos`         | YouTube embed links (admin-managed)  |

---

## ✨ Features Summary

| Feature                | Description                                          |
|------------------------|------------------------------------------------------|
| 🔐 Authentication      | Roll Number + Password, PHP sessions, no email/JWT  |
| 📚 Add Textbook         | Title, subject, author, semester, contact number    |
| 🔍 Browse Textbooks     | Filter by subject and semester                       |
| 📤 Upload Papers        | PDF only, max 10MB, stored in /uploads               |
| 📥 Download Papers      | Direct file download links                          |
| 🗓️ Study Planner        | JS-powered schedule with CSV export & print         |
| 🎬 Video Resources      | YouTube iframes embedded by admin                   |
| 👥 Admin: Users         | View & delete student accounts                      |
| 📚 Admin: Textbooks     | Approve / delete textbook listings                  |
| 🎬 Admin: Videos        | Add/delete YouTube video resources                  |

---

## 🔒 Security Features

- Passwords hashed with `password_hash()` (bcrypt)
- Passwords verified with `password_verify()`
- All DB queries use **prepared statements** (MySQLi)
- Session-based authentication — all pages check `$_SESSION`
- Admin pages protected by `requireAdmin()` — redirects if not admin
- File uploads validated by MIME type (not just extension)
- All user input sanitized with `htmlspecialchars` + `strip_tags`
- SQL injection prevented via parameterized queries

---

## 🌐 Page URLs

| Page                      | URL                                                      |
|--------------------------|----------------------------------------------------------|
| Home                      | `http://localhost/student_resource_system/`              |
| Register                  | `http://localhost/student_resource_system/register.php`  |
| Login                     | `http://localhost/student_resource_system/login.php`     |
| Student Dashboard         | `http://localhost/student_resource_system/dashboard.php` |
| Add Textbook              | `http://localhost/student_resource_system/add_textbook.php` |
| View Textbooks            | `http://localhost/student_resource_system/view_textbooks.php` |
| Upload Paper              | `http://localhost/student_resource_system/upload_paper.php` |
| View Papers               | `http://localhost/student_resource_system/view_papers.php` |
| Study Planner             | `http://localhost/student_resource_system/planner.php`   |
| Videos                    | `http://localhost/student_resource_system/videos.php`    |
| Admin Dashboard           | `http://localhost/student_resource_system/admin_dashboard.php` |
| Admin: Users              | `http://localhost/student_resource_system/admin_users.php` |
| Admin: Textbooks          | `http://localhost/student_resource_system/admin_textbooks.php` |
| Admin: Videos             | `http://localhost/student_resource_system/admin_videos.php` |

---

## 🛠️ Troubleshooting

**"Database connection failed"**
→ Make sure MySQL is running in XAMPP and `config.php` has correct credentials.

**"Failed to save file. Check permissions on /uploads folder"**
→ Create the `uploads/` folder manually inside the project directory.

**"Only PDF files are allowed"**
→ Only `.pdf` files are accepted for question paper uploads.

**Admin login not working**
→ Run the SQL file again. The admin password in the SQL is for `Admin@123`.
   To set a custom password, run in PhpMyAdmin:
   ```sql
   UPDATE users SET password = '$2y$10$...' WHERE roll_number = 'ADMIN001';
   ```
   Replace `$2y$10$...` with the output of `password_hash('yourpassword', PASSWORD_DEFAULT)`.

---

*Built with HTML, CSS, JavaScript, Core PHP, and MySQL — no frameworks, no APIs.*
