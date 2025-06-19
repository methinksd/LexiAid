-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS lexiaid_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE lexiaid_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    user_type ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- Legal resources table
CREATE TABLE IF NOT EXISTS legal_resources (
    resource_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    type ENUM('case', 'statute', 'article', 'note') NOT NULL,
    content TEXT NOT NULL,
    summary TEXT,
    jurisdiction VARCHAR(100),
    citation VARCHAR(255),
    tags JSON,
    date_published DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FULLTEXT INDEX idx_content (content),
    INDEX idx_type_jurisdiction (type, jurisdiction)
) ENGINE=InnoDB;

-- Tasks table
CREATE TABLE IF NOT EXISTS tasks (
    task_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    deadline DATETIME,
    completed BOOLEAN DEFAULT FALSE,
    completion_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_deadline (user_id, deadline)
) ENGINE=InnoDB;

-- Quizzes table
CREATE TABLE IF NOT EXISTS quizzes (
    quiz_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    topic VARCHAR(100) NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    details JSON,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_topic (user_id, topic)
) ENGINE=InnoDB;

-- Insert a default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, full_name, user_type) 
VALUES ('admin', 'admin@lexiaid.com', '$2y$10$8KzO7O1jFN.ZpuE4v5HGO.bAMtEBF2XfOqFNR0lFE9X9gmY6dZU5.', 'System Administrator', 'admin')
ON DUPLICATE KEY UPDATE user_id = user_id;

-- Insert a test student user (password: student123)
INSERT INTO users (username, email, password_hash, full_name, user_type)
VALUES ('student', 'student@lexiaid.com', '$2y$10$2Rl8L3TBh4xH3H9QSQZ9O.eF8QKVE0JYrMa8zqeXafF7yMc0bfNFi', 'Test Student', 'student')
ON DUPLICATE KEY UPDATE user_id = user_id;