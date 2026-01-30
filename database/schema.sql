-- Database Schema for Ciudad de San Jose QR Code Entry & Exit Monitoring System

-- Create database
CREATE DATABASE IF NOT EXISTS ciudad_de_san_jose;
USE ciudad_de_san_jose;

-- Homeowners table
CREATE TABLE IF NOT EXISTS homeowners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homeowner_id VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    qr_code VARCHAR(255) UNIQUE,
    current_status ENUM('IN', 'OUT') DEFAULT 'OUT',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_scan_time DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_homeowner_id (homeowner_id),
    INDEX idx_current_status (current_status),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Entry logs table
CREATE TABLE IF NOT EXISTS entry_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homeowner_id INT NOT NULL,
    action ENUM('IN', 'OUT') NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    device_name VARCHAR(100),
    device_id VARCHAR(50),
    guard_id INT,
    notes TEXT,
    INDEX idx_homeowner_id (homeowner_id),
    INDEX idx_action (action),
    INDEX idx_timestamp (timestamp),
    FOREIGN KEY (homeowner_id) REFERENCES homeowners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Guards/Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'guard', 'supervisor') DEFAULT 'guard',
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Scanner devices table
CREATE TABLE IF NOT EXISTS scanner_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id VARCHAR(50) UNIQUE NOT NULL,
    device_name VARCHAR(100) NOT NULL,
    location VARCHAR(100),
    status ENUM('online', 'offline', 'maintenance') DEFAULT 'offline',
    last_active DATETIME,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_device_id (device_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit log table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_timestamp (timestamp),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reports table
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_type ENUM('daily', 'weekly', 'monthly', 'custom') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    generated_by INT,
    file_path VARCHAR(255),
    file_format ENUM('pdf', 'excel') NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_report_type (report_type),
    INDEX idx_generated_by (generated_by),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, name, email, role, status) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin@ciudaddesanjose.com', 'admin', 'active')
ON DUPLICATE KEY UPDATE id=id;

-- Insert sample homeowners
INSERT INTO homeowners (homeowner_id, name, email, phone, address, qr_code, current_status, last_scan_time) VALUES
('HO-001', 'Juan Dela Cruz', 'juan.delacruz@email.com', '09171234567', 'Block 1 Lot 1', 'QR-HO-001', 'IN', NOW() - INTERVAL 5 MINUTE),
('HO-002', 'Maria Santos', 'maria.santos@email.com', '09181234567', 'Block 1 Lot 2', 'QR-HO-002', 'OUT', NOW() - INTERVAL 15 MINUTE),
('HO-003', 'Pedro Reyes', 'pedro.reyes@email.com', '09191234567', 'Block 1 Lot 3', 'QR-HO-003', 'IN', NOW() - INTERVAL 30 MINUTE),
('HO-004', 'Ana Garcia', 'ana.garcia@email.com', '09201234567', 'Block 1 Lot 4', 'QR-HO-004', 'OUT', NOW() - INTERVAL 45 MINUTE),
('HO-005', 'Carlos Mendoza', 'carlos.mendoza@email.com', '09211234567', 'Block 1 Lot 5', 'QR-HO-005', 'IN', NOW() - INTERVAL 1 HOUR),
('HO-006', 'Rosa Fernandez', 'rosa.fernandez@email.com', '09221234567', 'Block 2 Lot 1', 'QR-HO-006', 'IN', NOW() - INTERVAL 2 HOUR),
('HO-007', 'Miguel Torres', 'miguel.torres@email.com', '09231234567', 'Block 2 Lot 2', 'QR-HO-007', 'OUT', NOW() - INTERVAL 3 HOUR),
('HO-008', 'Sofia Ramirez', 'sofia.ramirez@email.com', '09241234567', 'Block 2 Lot 3', 'QR-HO-008', 'IN', NOW() - INTERVAL 4 HOUR)
ON DUPLICATE KEY UPDATE id=id;

-- Insert sample entry logs
INSERT INTO entry_logs (homeowner_id, action, timestamp, device_name) VALUES
(1, 'IN', NOW() - INTERVAL 5 MINUTE, 'Main Gate Scanner'),
(2, 'OUT', NOW() - INTERVAL 15 MINUTE, 'Back Gate Scanner'),
(3, 'IN', NOW() - INTERVAL 30 MINUTE, 'Main Gate Scanner'),
(4, 'OUT', NOW() - INTERVAL 45 MINUTE, 'Mobile Scanner 1'),
(5, 'IN', NOW() - INTERVAL 1 HOUR, 'Main Gate Scanner'),
(6, 'IN', NOW() - INTERVAL 2 HOUR, 'Main Gate Scanner'),
(7, 'OUT', NOW() - INTERVAL 3 HOUR, 'Back Gate Scanner'),
(8, 'IN', NOW() - INTERVAL 4 HOUR, 'Main Gate Scanner');

-- Insert sample scanner devices
INSERT INTO scanner_devices (device_id, device_name, location, status, last_active) VALUES
('SCANNER-001', 'Main Gate Scanner', 'Main Entrance Gate', 'online', NOW()),
('SCANNER-002', 'Back Gate Scanner', 'Back Entrance Gate', 'online', NOW()),
('SCANNER-003', 'Guard House Scanner', 'Guard House', 'offline', NOW() - INTERVAL 1 HOUR),
('SCANNER-004', 'Mobile Scanner 1', 'Mobile Unit', 'online', NOW())
ON DUPLICATE KEY UPDATE id=id;

-- Create trigger to automatically toggle homeowner status
DELIMITER $$

CREATE TRIGGER after_entry_log_insert
AFTER INSERT ON entry_logs
FOR EACH ROW
BEGIN
    UPDATE homeowners 
    SET current_status = NEW.action,
        last_scan_time = NEW.timestamp
    WHERE id = NEW.homeowner_id;
END$$

DELIMITER ;
