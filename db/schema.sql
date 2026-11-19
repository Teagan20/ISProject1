CREATE DATABASE IF NOT EXISTS isproject1 CHARACTER SET utf8mb4;
USE isproject1;
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(60) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  phone CHAR(10) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,   -- bcrypt hash, never plaintext
  role ENUM('student','admin') NOT NULL DEFAULT 'student',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  subject VARCHAR(100) NOT NULL,
  message TEXT NOT NULL,
  priority ENUM('low','medium','high') DEFAULT 'medium',
  status ENUM('open','closed') DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE login_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(120), ip VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX(email), INDEX(ip)
);
CREATE TABLE audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL, action VARCHAR(200), ip VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- App DB user (CHANGE THE PASSWORD and match it in inc/bootstrap.php or the DB_PASS env var)
CREATE USER IF NOT EXISTS 'isp_app'@'localhost' IDENTIFIED BY 'ChangeMe_Str0ng!';
GRANT SELECT,INSERT,UPDATE,DELETE ON isproject1.* TO 'isp_app'@'localhost';
FLUSH PRIVILEGES;
