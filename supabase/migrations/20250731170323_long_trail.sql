-- StudyFetch Database Schema
-- Run this SQL in phpMyAdmin to create the database structure

CREATE DATABASE IF NOT EXISTS studyfetch;
USE studyfetch;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PDFs table
CREATE TABLE pdfs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    chapter VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Questions table
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pdf_id INT,
    type ENUM('mcq', 'short_answer', 'long_answer', 'fill_blanks', 'true_false', 'assertion_reason') NOT NULL,
    difficulty ENUM('easy', 'medium', 'hard') NOT NULL,
    question_text TEXT NOT NULL,
    marks INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pdf_id) REFERENCES pdfs(id) ON DELETE CASCADE
);

-- Flashcards table
CREATE TABLE flashcards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pdf_id INT,
    front TEXT NOT NULL,
    back TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pdf_id) REFERENCES pdfs(id) ON DELETE CASCADE
);

-- Chat logs table
CREATE TABLE chat_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    message TEXT NOT NULL,
    response TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@studyfetch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample PDFs (for testing)
INSERT INTO pdfs (title, filename, subject, chapter) VALUES 
('Introduction to Physics', 'physics_intro.pdf', 'Physics', 'Chapter 1: Mechanics'),
('Basic Chemistry', 'chemistry_basics.pdf', 'Chemistry', 'Chapter 2: Atomic Structure'),
('Mathematics Fundamentals', 'math_fundamentals.pdf', 'Mathematics', 'Chapter 3: Algebra');

-- Insert sample flashcards
INSERT INTO flashcards (pdf_id, front, back) VALUES 
(1, 'What is Newton\'s First Law?', 'An object at rest stays at rest and an object in motion stays in motion unless acted upon by an external force.'),
(1, 'Define Velocity', 'Velocity is the rate of change of displacement with respect to time.'),
(2, 'What is an Atom?', 'An atom is the smallest unit of matter that retains the properties of an element.'),
(2, 'Define Electron', 'An electron is a negatively charged subatomic particle that orbits the nucleus of an atom.');