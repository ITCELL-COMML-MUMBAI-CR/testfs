-- Migration to create system_cache table for background refresh system
-- This table stores cached data, automation timestamps, and system state

CREATE TABLE IF NOT EXISTS system_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cache_key VARCHAR(100) NOT NULL UNIQUE,
    cache_data JSON NOT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_cache_key (cache_key),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='System cache for background refresh and automation';

-- Insert initial cache entries
INSERT INTO system_cache (cache_key, cache_data) VALUES
('last_automation_run', JSON_OBJECT('last_run', NULL, 'status', 'initialized')),
('last_heartbeat', JSON_OBJECT('timestamp', NULL, 'status', 'initialized')),
('system_stats', JSON_OBJECT(
    'total_active_tickets', 0,
    'high_priority_tickets', 0, 
    'sla_violations', 0,
    'updated_at', NOW()
))
ON DUPLICATE KEY UPDATE 
    cache_data = VALUES(cache_data),
    updated_at = CURRENT_TIMESTAMP;