-- Visitor Logs Table Schema
-- This table stores information about external visitors (non-residents)

CREATE TABLE IF NOT EXISTS visitor_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visitor_name VARCHAR(100) NOT NULL,
    visitor_type ENUM('Personal', 'Professional', 'Service') DEFAULT 'Personal',
    company VARCHAR(100),
    homeowner_id INT NOT NULL, -- The resident being visited
    person_to_visit VARCHAR(100), -- Redundant name for display if needed
    gate VARCHAR(50),
    purpose TEXT,
    time_in DATETIME DEFAULT CURRENT_TIMESTAMP,
    time_out DATETIME,
    status ENUM('INSIDE', 'OUT') DEFAULT 'INSIDE',
    guard_id INT NULL, -- Guard who registered the visitor (Optional)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_homeowner_id (homeowner_id),
    INDEX idx_visitor_name (visitor_name),
    INDEX idx_status (status),
    INDEX idx_time_in (time_in),
    
    FOREIGN KEY (homeowner_id) REFERENCES homeowners(id) ON DELETE CASCADE,
    FOREIGN KEY (guard_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
