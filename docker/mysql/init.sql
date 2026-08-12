# MySQL initialization script for Docker
# This runs automatically when the MySQL container starts for the first time

-- Create a dedicated read/write user (optional, root is sufficient for dev)
CREATE USER IF NOT EXISTS 'skill_user'@'%' IDENTIFIED BY 'skill_pass';
GRANT ALL PRIVILEGES ON skill_matching_system.* TO 'skill_user'@'%';
FLUSH PRIVILEGES;
