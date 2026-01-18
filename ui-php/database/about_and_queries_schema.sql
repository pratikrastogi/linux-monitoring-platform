-- About Us Content Table
CREATE TABLE IF NOT EXISTS about_us (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL DEFAULT 'About KubeArena',
    content LONGTEXT NOT NULL,
    mission TEXT,
    vision TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default about us content
INSERT INTO about_us (title, content, mission, vision) VALUES
('About KubeArena', 
'<h2>Welcome to KubeArena</h2>
<p>KubeArena is a comprehensive learning platform dedicated to providing hands-on experience with Linux, Kubernetes, Docker, and DevOps technologies. Our mission is to make advanced technical education accessible to everyone, regardless of their background or financial situation.</p>

<h3>What We Offer</h3>
<ul>
<li><strong>Free Hands-on Labs:</strong> Practice on real servers with interactive terminal access</li>
<li><strong>RHEL Certification Prep:</strong> Comprehensive RHCSA and RHCE training materials</li>
<li><strong>Kubernetes Training:</strong> Learn container orchestration with practical exercises</li>
<li><strong>Docker Mastery:</strong> Build, deploy, and manage containerized applications</li>
<li><strong>DevOps Excellence:</strong> CI/CD pipelines, automation, and cloud technologies</li>
</ul>

<h3>Why Choose KubeArena?</h3>
<p>Unlike traditional learning platforms, KubeArena provides actual server access where you can practice commands, break things, and learn from real-world scenarios. Our labs are designed by industry professionals with years of production experience.</p>',

'To democratize technical education by providing free, high-quality hands-on training in Linux, Kubernetes, Docker, and DevOps technologies to learners worldwide.',

'To become the leading platform for practical IT education, empowering millions to build successful careers in cloud computing and DevOps engineering.'
);

-- User Queries Table
CREATE TABLE IF NOT EXISTS user_queries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    email VARCHAR(255),
    requirement TEXT NOT NULL,
    status ENUM('pending', 'in-progress', 'resolved', 'closed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME,
    notes TEXT,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
