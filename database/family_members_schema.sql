-- Family Members Table Schema
-- This table stores family members linked to a primary homeowner

CREATE TABLE IF NOT EXISTS family_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homeowner_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    role ENUM('Owner', 'Spouse', 'Child', 'Relative', 'Caregiver', 'Other') DEFAULT 'Child',
    date_of_birth DATE,
    relationship_notes TEXT,
    
    -- QR Code Information
    qr_code VARCHAR(255) UNIQUE,
    qr_token VARCHAR(128) UNIQUE,
    qr_expiry DATETIME,
    qr_last_generated DATETIME,
    
    -- Access Control
    access_status ENUM('active', 'disabled', 'suspended') DEFAULT 'active',
    allowed_entry_points JSON, -- e.g., ["Entry Gate", "Exit Gate"]
    allowed_hours_start TIME, -- e.g., "06:00:00"
    allowed_hours_end TIME, -- e.g., "22:00:00"
    
    -- Tracking
    current_status ENUM('IN', 'OUT') DEFAULT 'OUT',
    last_scan_time DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_homeowner_id (homeowner_id),
    INDEX idx_qr_token (qr_token),
    INDEX idx_access_status (access_status),
    INDEX idx_current_status (current_status),
    
    FOREIGN KEY (homeowner_id) REFERENCES homeowners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Family Member Activity Logs Table
CREATE TABLE IF NOT EXISTS family_member_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    family_member_id INT NOT NULL,
    homeowner_id INT NOT NULL,
    action ENUM('IN', 'OUT') NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    device_name VARCHAR(100),
    device_id VARCHAR(50),
    entry_point VARCHAR(100),
    guard_id INT,
    notes TEXT,
    
    INDEX idx_family_member_id (family_member_id),
    INDEX idx_homeowner_id (homeowner_id),
    INDEX idx_action (action),
    INDEX idx_timestamp (timestamp),
    
    FOREIGN KEY (family_member_id) REFERENCES family_members(id) ON DELETE CASCADE,
    FOREIGN KEY (homeowner_id) REFERENCES homeowners(id) ON DELETE CASCADE,
    FOREIGN KEY (guard_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trigger to update family member status on scan
DELIMITER $$

CREATE TRIGGER after_family_member_log_insert
AFTER INSERT ON family_member_logs
FOR EACH ROW
BEGIN
    UPDATE family_members 
    SET current_status = NEW.action,
        last_scan_time = NEW.timestamp
    WHERE id = NEW.family_member_id;
END$$

DELIMITER ;
