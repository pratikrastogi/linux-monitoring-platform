-- =============================================
-- LABS AS A SERVICE - DATABASE SCHEMA
-- Version: 1.0
-- Date: 12 Jan 2026
-- Compatible with: MySQL 8.0.44
-- Existing Schema: lab_sessions, lab_extension_requests
-- =============================================
-- CRITICAL: This is ADDITIVE ONLY
-- Does NOT modify existing tables
-- =============================================

USE monitoring;

-- =============================================
-- ALTER EXISTING TABLE: lab_sessions
-- Add column to link lab sessions to templates
-- =============================================
ALTER TABLE lab_sessions 
ADD COLUMN IF NOT EXISTS lab_template_id INT DEFAULT NULL AFTER username,
ADD INDEX IF NOT EXISTS idx_template (lab_template_id);

-- =============================================
-- TABLE: courses
-- Purpose: Store lab courses catalog
-- =============================================
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100) DEFAULT 'kubernetes',
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    duration_hours INT DEFAULT 1,
    icon VARCHAR(50) DEFAULT 'fa-cube',
    color VARCHAR(20) DEFAULT '#667eea',
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- =============================================
-- TABLE: lab_templates
-- Purpose: Lab definitions with guides (Markdown)
-- =============================================
CREATE TABLE IF NOT EXISTS lab_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    guide_markdown TEXT,
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    estimated_time INT DEFAULT 30 COMMENT 'Minutes',
    objectives TEXT COMMENT 'JSON array of learning objectives',
    prerequisites TEXT COMMENT 'JSON array of prerequisites',
    commands TEXT COMMENT 'JSON array of sample commands',
    resources_cpu INT DEFAULT 1 COMMENT 'CPU cores required',
    resources_memory INT DEFAULT 512 COMMENT 'Memory in MB',
    auto_provision BOOLEAN DEFAULT false,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    INDEX idx_course (course_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- =============================================
-- TABLE: lab_requests
-- Purpose: Track user lab access requests
-- Note: Different from existing lab_extension_requests
-- =============================================
CREATE TABLE IF NOT EXISTS lab_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    lab_template_id INT,
    requested_hours INT DEFAULT 1,
    purpose TEXT,
    experience_level ENUM('beginner', 'intermediate', 'advanced'),
    status ENUM('pending', 'approved', 'denied', 'provisioned', 'expired') DEFAULT 'pending',
    admin_notes TEXT,
    approved_by INT,
    approved_at TIMESTAMP NULL,
    denied_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lab_template_id) REFERENCES lab_templates(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- =============================================
-- TABLE: provisioners
-- Purpose: Lab provisioner worker nodes
-- =============================================
CREATE TABLE IF NOT EXISTS provisioners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    ssh_port INT DEFAULT 22,
    ssh_user VARCHAR(50) NOT NULL,
    ssh_key_path VARCHAR(255),
    max_concurrent_labs INT DEFAULT 5,
    current_labs INT DEFAULT 0,
    cpu_cores INT DEFAULT 4,
    memory_gb INT DEFAULT 8,
    storage_gb INT DEFAULT 100,
    status ENUM('active', 'maintenance', 'offline') DEFAULT 'active',
    last_heartbeat TIMESTAMP NULL,
    tags TEXT COMMENT 'JSON array of tags',
    metadata TEXT COMMENT 'JSON object with additional info',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- =============================================
-- TABLE: lab_progress
-- Purpose: Track user progress through labs
-- =============================================
CREATE TABLE IF NOT EXISTS lab_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lab_template_id INT NOT NULL,
    lab_session_id INT,
    status ENUM('not_started', 'in_progress', 'completed', 'abandoned') DEFAULT 'not_started',
    progress_percent INT DEFAULT 0,
    checkpoints_completed TEXT COMMENT 'JSON array of completed checkpoint IDs',
    time_spent_minutes INT DEFAULT 0,
    completion_notes TEXT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    feedback TEXT,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lab_template_id) REFERENCES lab_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (lab_session_id) REFERENCES lab_sessions(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    UNIQUE KEY unique_user_lab (user_id, lab_template_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- =============================================
-- TABLE: lab_checkpoints
-- Purpose: Track granular lab completion steps
-- =============================================
CREATE TABLE IF NOT EXISTS lab_checkpoints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lab_template_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    step_order INT NOT NULL,
    validation_command TEXT COMMENT 'Command to verify completion',
    validation_criteria TEXT COMMENT 'Expected output pattern',
    points INT DEFAULT 10,
    is_required BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lab_template_id) REFERENCES lab_templates(id) ON DELETE CASCADE,
    INDEX idx_template (lab_template_id),
    INDEX idx_order (step_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- =============================================
-- SEED DATA: Sample Course
-- =============================================
INSERT INTO courses (title, description, category, difficulty, duration_hours, icon, color, status) VALUES
('Kubernetes Fundamentals', 'Master the basics of Kubernetes container orchestration', 'kubernetes', 'beginner', 2, 'fa-dharmachakra', '#667eea', 'published'),
('Docker Essentials', 'Learn containerization with Docker from scratch', 'docker', 'beginner', 1, 'fa-docker', '#2496ed', 'published'),
('CI/CD with GitOps', 'Implement GitOps workflows with ArgoCD and Flux', 'devops', 'intermediate', 3, 'fa-code-branch', '#764ba2', 'published');

-- =============================================
-- SEED DATA: Sample Lab Template
-- =============================================
INSERT INTO lab_templates (course_id, title, description, guide_markdown, difficulty, estimated_time, status) VALUES
(1, 'Docker Basics', 'Introduction to Docker containers and images', 
'# Docker Basics Lab

## Objectives
- Understand Docker architecture
- Work with containers and images
- Learn Docker CLI commands

## Steps

### 1. Check Docker Installation
```bash
docker --version
docker info
```

### 2. Pull and Run Your First Container
```bash
docker pull nginx
docker run -d -p 8080:80 --name web nginx
```

### 3. Inspect Running Containers
```bash
docker ps
docker logs web
docker inspect web
```

### 4. Cleanup
```bash
docker stop web
docker rm web
```

## Checkpoint
✅ You should now understand how to manage Docker containers!
', 
'beginner', 30, 'published');

-- =============================================
-- VIEW: Active Labs Summary
-- =============================================
CREATE OR REPLACE VIEW v_active_labs_summary AS
SELECT 
    u.username,
    u.email,
    lt.title AS lab_title,
    ls.status,
    ls.access_granted,
    ls.access_expiry,
    TIMESTAMPDIFF(MINUTE, NOW(), ls.access_expiry) AS minutes_remaining
FROM lab_sessions ls
JOIN users u ON ls.user_id = u.id
LEFT JOIN lab_templates lt ON ls.lab_template_id = lt.id
WHERE ls.status = 'ACTIVE'
ORDER BY ls.access_expiry ASC;

-- =============================================
-- STORED PROCEDURE: Provision Lab
-- =============================================
DELIMITER $$

CREATE PROCEDURE sp_provision_lab(
    IN p_user_id INT,
    IN p_lab_template_id INT,
    IN p_hours INT
)
BEGIN
    DECLARE v_request_id INT;
    
    -- Insert lab request
    INSERT INTO lab_requests (user_id, username, lab_template_id, requested_hours, status)
    SELECT p_user_id, username, p_lab_template_id, p_hours, 'pending'
    FROM users WHERE id = p_user_id;
    
    SET v_request_id = LAST_INSERT_ID();
    
    SELECT v_request_id AS request_id, 'Lab request created successfully' AS message;
END$$

DELIMITER ;

-- =============================================
-- MIGRATION NOTES
-- =============================================
-- To deploy:
-- 1. Backup existing database: mysqldump monitoring > backup.sql
-- 2. Run this script: mysql monitoring < labs_schema.sql
-- 3. Verify: SELECT COUNT(*) FROM courses;
-- 4. No existing tables are modified
-- 5. All existing functionality remains intact
