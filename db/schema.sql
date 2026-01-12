CREATE DATABASE monitoring;
USE monitoring;

CREATE TABLE servers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hostname VARCHAR(255),
  ip_address VARCHAR(50),
  ssh_user VARCHAR(50),
  ssh_port INT DEFAULT 22,
  added_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE server_metrics (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  server_id INT,
  os_version VARCHAR(255),
  virtualization VARCHAR(50),
  uptime VARCHAR(100),
  sshd_status VARCHAR(20),
  cpu_usage FLOAT,
  mem_usage FLOAT,
  disk_usage FLOAT,
  reachable BOOLEAN,
  collected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE alerts (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  server_id INT,
  alert_type VARCHAR(50),
  message TEXT,
  active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE provision_target (
  id INT AUTO_INCREMENT PRIMARY KEY,
  target_ip VARCHAR(50) NOT NULL,
  target_user VARCHAR(100) NOT NULL,
  target_password VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE provisioners (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL UNIQUE,
  ip_address VARCHAR(50) NOT NULL,
  ssh_user VARCHAR(100) NOT NULL,
  ssh_password VARCHAR(255),
  ssh_key TEXT,
  max_pods INT DEFAULT 10,
  cpu_total VARCHAR(50),
  memory_total VARCHAR(50),
  current_pods INT DEFAULT 0,
  status VARCHAR(50) DEFAULT 'active',
  last_health_check TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

