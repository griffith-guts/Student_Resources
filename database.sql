-- =============================================
-- Student Resource Sharing System - Database
-- =============================================

CREATE DATABASE IF NOT EXISTS student_resource_db;
USE student_resource_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    roll_number VARCHAR(50) NOT NULL UNIQUE,
    department VARCHAR(100) NOT NULL,
    semester INT NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Textbooks Table
CREATE TABLE IF NOT EXISTS textbooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    author VARCHAR(100) NOT NULL,
    semester INT NOT NULL,
    contact VARCHAR(20) NOT NULL,
    status ENUM('pending', 'approved') DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Question Papers Table
CREATE TABLE IF NOT EXISTS question_papers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Videos Table
CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    youtube_link VARCHAR(300) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Admin User (password: Admin@123)
INSERT INTO users (name, roll_number, department, semester, password, role)
VALUES ('Administrator', 'ADMIN001', 'Administration', 1, '$2y$10$rBNVzv1lKgqfP/BzWxOGFukei3xKWp8W5iGZQH09iZVM.RVw/wbU2', 'admin')
ON DUPLICATE KEY UPDATE name = name;

-- Note: The hashed password above is for 'Admin@123'
-- You can change it by running: password_hash('your_new_password', PASSWORD_DEFAULT) in PHP
