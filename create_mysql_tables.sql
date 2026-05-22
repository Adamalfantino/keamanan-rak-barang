-- =====================================================
-- SMART RACK SECURITY SYSTEM - MySQL Database Schema
-- =====================================================
-- Generated: 2026-04-29
-- Compatible with: MySQL 8.0+, MariaDB 10.3+
-- =====================================================

-- Create database
CREATE DATABASE IF NOT EXISTS smart_rack_security 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE smart_rack_security;

-- =====================================================
-- 1. USERS TABLE
-- =====================================================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'operator', 'viewer') DEFAULT 'viewer',
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_role_active (role, is_active)
);

-- =====================================================
-- 2. DEVICES TABLE (Arduino/LoRa Nodes)
-- =====================================================
CREATE TABLE devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    device_id VARCHAR(50) UNIQUE NOT NULL COMMENT 'Node ID seperti LORA_001',
    name VARCHAR(255) NOT NULL,
    type ENUM('lora_node', 'gateway', 'sensor_hub') DEFAULT 'lora_node',
    location VARCHAR(255) NULL COMMENT 'Lokasi fisik device',
    ip_address VARCHAR(45) NULL,
    mac_address VARCHAR(17) NULL,
    firmware_version VARCHAR(50) NULL,
    battery_level DECIMAL(5,2) DEFAULT 100.00 COMMENT 'Battery percentage',
    signal_strength INT DEFAULT 0 COMMENT 'Signal strength in dBm',
    is_online BOOLEAN DEFAULT FALSE,
    last_seen_at TIMESTAMP NULL,
    configuration JSON NULL COMMENT 'Device specific config',
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_device_id (device_id),
    INDEX idx_type_online (type, is_online),
    INDEX idx_last_seen (last_seen_at)
);

-- =====================================================
-- 3. SENSORS TABLE
-- =====================================================
CREATE TABLE sensors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    device_id BIGINT UNSIGNED NOT NULL,
    sensor_type ENUM('pir', 'reed_switch', 'vibration', 'temperature', 'humidity', 'door_access') NOT NULL,
    sensor_name VARCHAR(255) NOT NULL,
    pin_number INT NULL COMMENT 'GPIO pin number',
    unit VARCHAR(20) NULL COMMENT 'Measurement unit',
    min_value DECIMAL(10,4) NULL,
    max_value DECIMAL(10,4) NULL,
    threshold DECIMAL(10,4) NULL COMMENT 'Alert threshold',
    calibration_offset DECIMAL(10,4) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    INDEX idx_device_sensor (device_id, sensor_type),
    INDEX idx_sensor_type_active (sensor_type, is_active)
);

-- =====================================================
-- 4. PIR READINGS TABLE (Motion Detection)
-- =====================================================
CREATE TABLE pir_readings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    device_id BIGINT UNSIGNED NOT NULL,
    motion_detected BOOLEAN DEFAULT FALSE COMMENT 'Gerakan terdeteksi',
    motion_intensity INT DEFAULT 0 COMMENT 'Intensitas gerakan (0-100)',
    duration_seconds INT DEFAULT 0 COMMENT 'Durasi gerakan dalam detik',
    is_authorized_time BOOLEAN DEFAULT TRUE COMMENT 'Apakah dalam jam kerja',
    is_suspicious BOOLEAN DEFAULT FALSE COMMENT 'Gerakan mencurigakan',
    motion_type ENUM('normal', 'suspicious', 'unauthorized') DEFAULT 'normal',
    detection_zone VARCHAR(50) NULL COMMENT 'Area deteksi (front, back, side, center)',
    metadata JSON NULL COMMENT 'Data tambahan sensor',
    motion_start TIMESTAMP NULL COMMENT 'Waktu mulai gerakan',
    motion_end TIMESTAMP NULL COMMENT 'Waktu selesai gerakan',
    recorded_at TIMESTAMP NOT NULL COMMENT 'Waktu pembacaan sensor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    INDEX idx_device_recorded (device_id, recorded_at),
    INDEX idx_motion_suspicious (motion_detected, is_suspicious),
    INDEX idx_authorized_type (is_authorized_time, motion_type),
    INDEX idx_recorded_at (recorded_at)
);

-- =====================================================
-- 5. DOOR READINGS TABLE (Reed Switch)
-- =====================================================
CREATE TABLE door_readings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    device_id BIGINT UNSIGNED NOT NULL,
    door_open BOOLEAN DEFAULT FALSE COMMENT 'Status pintu terbuka',
    is_authorized_access BOOLEAN DEFAULT TRUE COMMENT 'Akses yang sah',
    is_forced_entry BOOLEAN DEFAULT FALSE COMMENT 'Pembukaan paksa',
    access_type ENUM('normal', 'unauthorized', 'forced', 'maintenance') DEFAULT 'normal',
    door_location VARCHAR(100) NULL COMMENT 'front_panel, back_panel, side_door, main_door',
    open_duration_seconds INT DEFAULT 0 COMMENT 'Durasi terbuka dalam detik',
    proper_closure BOOLEAN DEFAULT TRUE COMMENT 'Apakah ditutup dengan benar',
    access_card_data JSON NULL COMMENT 'Data kartu akses jika ada',
    metadata JSON NULL COMMENT 'Data tambahan sensor',
    door_opened_at TIMESTAMP NULL COMMENT 'Waktu pintu dibuka',
    door_closed_at TIMESTAMP NULL COMMENT 'Waktu pintu ditutup',
    recorded_at TIMESTAMP NOT NULL COMMENT 'Waktu pembacaan sensor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    INDEX idx_device_recorded (device_id, recorded_at),
    INDEX idx_door_authorized (door_open, is_authorized_access),
    INDEX idx_access_location (access_type, door_location),
    INDEX idx_forced_entry (is_forced_entry, proper_closure),
    INDEX idx_recorded_at (recorded_at)
);

-- =====================================================
-- 6. VIBRATION READINGS TABLE (SW-420 Sensor)
-- =====================================================
CREATE TABLE vibration_readings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    device_id BIGINT UNSIGNED NOT NULL,
    x_axis DECIMAL(8,4) NOT NULL COMMENT 'Getaran sumbu X',
    y_axis DECIMAL(8,4) NOT NULL COMMENT 'Getaran sumbu Y',
    z_axis DECIMAL(8,4) NOT NULL COMMENT 'Getaran sumbu Z',
    magnitude DECIMAL(8,4) GENERATED ALWAYS AS (SQRT(POW(x_axis,2) + POW(y_axis,2) + POW(z_axis,2))) STORED COMMENT 'Total magnitude getaran',
    is_abnormal BOOLEAN DEFAULT FALSE COMMENT 'Status getaran abnormal',
    threshold DECIMAL(8,4) DEFAULT 2.0000 COMMENT 'Batas normal getaran',
    status ENUM('normal', 'warning', 'critical') DEFAULT 'normal',
    metadata JSON NULL COMMENT 'Data tambahan sensor',
    recorded_at TIMESTAMP NOT NULL COMMENT 'Waktu pembacaan sensor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    INDEX idx_device_recorded (device_id, recorded_at),
    INDEX idx_abnormal_status (is_abnormal, status),
    INDEX idx_magnitude (magnitude),
    INDEX idx_recorded_at (recorded_at)
);

-- =====================================================
-- 7. DOOR ACCESS READINGS TABLE (Detailed Access Log)
-- =====================================================
CREATE TABLE door_access_readings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    device_id BIGINT UNSIGNED NOT NULL,
    door_opened BOOLEAN DEFAULT FALSE,
    user_id_card VARCHAR(100) NULL COMMENT 'ID Card atau badge number',
    duration_seconds INT DEFAULT 0,
    access_method ENUM('keycard', 'manual', 'forced', 'maintenance') DEFAULT 'manual',
    door_location VARCHAR(100) NULL,
    is_authorized BOOLEAN DEFAULT TRUE,
    access_granted_by VARCHAR(100) NULL COMMENT 'Who granted access',
    metadata JSON NULL,
    recorded_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    INDEX idx_device_recorded (device_id, recorded_at),
    INDEX idx_user_card (user_id_card),
    INDEX idx_access_method (access_method, is_authorized),
    INDEX idx_recorded_at (recorded_at)
);

-- =====================================================
-- 8. LORA MESSAGES TABLE (Communication Log)
-- =====================================================
CREATE TABLE lora_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    node_id VARCHAR(50) NOT NULL COMMENT 'Source node ID',
    gateway_id VARCHAR(50) NULL COMMENT 'Gateway yang menerima',
    message_type ENUM('sensor_data', 'heartbeat', 'command', 'ack', 'error') NOT NULL,
    raw_payload TEXT NOT NULL COMMENT 'Raw LoRa message',
    parsed_data JSON NULL COMMENT 'Parsed message data',
    rssi INT NULL COMMENT 'Signal strength',
    snr DECIMAL(5,2) NULL COMMENT 'Signal to noise ratio',
    frequency DECIMAL(10,2) NULL COMMENT 'Frequency in Hz',
    is_processed BOOLEAN DEFAULT FALSE,
    processing_error TEXT NULL,
    received_at TIMESTAMP NOT NULL,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_node_received (node_id, received_at),
    INDEX idx_message_type (message_type, is_processed),
    INDEX idx_received_at (received_at)
);

-- =====================================================
-- 9. SENSOR READINGS TABLE (General Sensor Data)
-- =====================================================
CREATE TABLE sensor_readings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sensor_id BIGINT UNSIGNED NOT NULL,
    device_id BIGINT UNSIGNED NOT NULL,
    value DECIMAL(15,4) NOT NULL COMMENT 'Processed sensor value',
    raw_value VARCHAR(255) NULL COMMENT 'Raw sensor reading',
    status ENUM('normal', 'warning', 'critical', 'error') DEFAULT 'normal',
    battery_level DECIMAL(5,2) NULL COMMENT 'Device battery level',
    signal_strength INT NULL COMMENT 'Signal strength in dBm',
    metadata JSON NULL,
    reading_time TIMESTAMP NOT NULL,
    is_processed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sensor_id) REFERENCES sensors(id) ON DELETE CASCADE,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    INDEX idx_sensor_reading_time (sensor_id, reading_time),
    INDEX idx_device_reading_time (device_id, reading_time),
    INDEX idx_status (status),
    INDEX idx_reading_time (reading_time)
);

-- =====================================================
-- 10. ALERTS TABLE (System Alerts & Notifications)
-- =====================================================
CREATE TABLE alerts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    device_id BIGINT UNSIGNED NULL,
    alert_type ENUM('motion', 'door_access', 'vibration', 'system', 'security') NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    source_table VARCHAR(50) NULL COMMENT 'Table yang memicu alert',
    source_id BIGINT UNSIGNED NULL COMMENT 'ID record yang memicu alert',
    is_acknowledged BOOLEAN DEFAULT FALSE,
    acknowledged_by BIGINT UNSIGNED NULL,
    acknowledged_at TIMESTAMP NULL,
    metadata JSON NULL,
    triggered_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE SET NULL,
    FOREIGN KEY (acknowledged_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_device_triggered (device_id, triggered_at),
    INDEX idx_type_severity (alert_type, severity),
    INDEX idx_acknowledged (is_acknowledged, acknowledged_at),
    INDEX idx_triggered_at (triggered_at)
);

-- =====================================================
-- 11. ACTIVITY LOGS TABLE (System Activity)
-- =====================================================
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    device_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL COMMENT 'Action performed',
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE SET NULL,
    INDEX idx_user_created (user_id, created_at),
    INDEX idx_device_created (device_id, created_at),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- =====================================================
-- 12. SYSTEM SETTINGS TABLE
-- =====================================================
CREATE TABLE system_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NULL,
    setting_type ENUM('string', 'integer', 'float', 'boolean', 'json') DEFAULT 'string',
    description TEXT NULL,
    is_public BOOLEAN DEFAULT FALSE COMMENT 'Can be accessed by non-admin users',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_key_public (setting_key, is_public)
);
-- =====
================================================
-- SAMPLE DATA INSERTION
-- =====================================================

-- Insert default admin user
INSERT INTO users (name, email, password, role, is_active) VALUES 
('System Admin', 'admin@smartrack.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1),
('Operator', 'operator@smartrack.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'operator', 1);

-- Insert sample devices
INSERT INTO devices (device_id, name, type, location, battery_level, is_online) VALUES 
('LORA_001', 'Smart Rack Node 1', 'lora_node', 'Server Room A - Rack 1', 85.50, 1),
('LORA_002', 'Smart Rack Node 2', 'lora_node', 'Server Room A - Rack 2', 92.30, 1),
('GATEWAY_001', 'LoRa Gateway Main', 'gateway', 'Server Room A - Central', 100.00, 1);

-- Insert sensors for each device
INSERT INTO sensors (device_id, sensor_type, sensor_name, pin_number, unit, threshold, is_active) VALUES 
-- Device LORA_001 sensors
(1, 'pir', 'PIR Motion Sensor', 33, 'boolean', 1, 1),
(1, 'reed_switch', 'Door Reed Switch', 32, 'boolean', 1, 1),
(1, 'vibration', 'Vibration Sensor SW-420', 34, 'g-force', 2.0, 1),
-- Device LORA_002 sensors  
(2, 'pir', 'PIR Motion Sensor', 33, 'boolean', 1, 1),
(2, 'reed_switch', 'Door Reed Switch', 32, 'boolean', 1, 1),
(2, 'vibration', 'Vibration Sensor SW-420', 34, 'g-force', 2.5, 1);

-- Insert system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES 
('system_name', 'Smart Rack Security System', 'string', 'System display name', 1),
('alert_email', 'alerts@smartrack.com', 'string', 'Email for system alerts', 0),
('work_hours_start', '08:00', 'string', 'Work hours start time', 1),
('work_hours_end', '17:00', 'string', 'Work hours end time', 1),
('vibration_threshold_default', '2.0', 'float', 'Default vibration threshold', 1),
('heartbeat_interval', '300', 'integer', 'Heartbeat interval in seconds', 1),
('lora_frequency', '433000000', 'integer', 'LoRa frequency in Hz', 0),
('max_door_open_duration', '600', 'integer', 'Max door open duration in seconds', 1);

-- =====================================================
-- TRIGGERS FOR AUTOMATIC CALCULATIONS
-- =====================================================

-- Trigger untuk update magnitude pada vibration_readings
DELIMITER $$
CREATE TRIGGER tr_vibration_magnitude_update
    BEFORE UPDATE ON vibration_readings
    FOR EACH ROW
BEGIN
    -- Magnitude sudah dihitung otomatis dengan GENERATED ALWAYS AS
    -- Update status berdasarkan magnitude vs threshold
    IF (SQRT(POW(NEW.x_axis,2) + POW(NEW.y_axis,2) + POW(NEW.z_axis,2))) > NEW.threshold THEN
        SET NEW.is_abnormal = TRUE;
        IF (SQRT(POW(NEW.x_axis,2) + POW(NEW.y_axis,2) + POW(NEW.z_axis,2))) > (NEW.threshold * 1.5) THEN
            SET NEW.status = 'critical';
        ELSE
            SET NEW.status = 'warning';
        END IF;
    ELSE
        SET NEW.is_abnormal = FALSE;
        SET NEW.status = 'normal';
    END IF;
END$$

-- Trigger untuk insert vibration_readings
CREATE TRIGGER tr_vibration_magnitude_insert
    BEFORE INSERT ON vibration_readings
    FOR EACH ROW
BEGIN
    -- Update status berdasarkan magnitude vs threshold
    IF (SQRT(POW(NEW.x_axis,2) + POW(NEW.y_axis,2) + POW(NEW.z_axis,2))) > NEW.threshold THEN
        SET NEW.is_abnormal = TRUE;
        IF (SQRT(POW(NEW.x_axis,2) + POW(NEW.y_axis,2) + POW(NEW.z_axis,2))) > (NEW.threshold * 1.5) THEN
            SET NEW.status = 'critical';
        ELSE
            SET NEW.status = 'warning';
        END IF;
    ELSE
        SET NEW.is_abnormal = FALSE;
        SET NEW.status = 'normal';
    END IF;
END$$

-- Trigger untuk auto-create alerts pada data abnormal
CREATE TRIGGER tr_create_vibration_alert
    AFTER INSERT ON vibration_readings
    FOR EACH ROW
BEGIN
    IF NEW.is_abnormal = TRUE THEN
        INSERT INTO alerts (
            device_id, 
            alert_type, 
            severity, 
            title, 
            message, 
            source_table, 
            source_id, 
            triggered_at
        ) VALUES (
            NEW.device_id,
            'vibration',
            CASE 
                WHEN NEW.status = 'critical' THEN 'critical'
                WHEN NEW.status = 'warning' THEN 'high'
                ELSE 'medium'
            END,
            CONCAT('Abnormal Vibration Detected - ', NEW.status),
            CONCAT('Vibration magnitude ', NEW.magnitude, ' exceeds threshold ', NEW.threshold, ' on device ', (SELECT device_id FROM devices WHERE id = NEW.device_id)),
            'vibration_readings',
            NEW.id,
            NEW.recorded_at
        );
    END IF;
END$$

-- Trigger untuk PIR suspicious motion alerts
CREATE TRIGGER tr_create_pir_alert
    AFTER INSERT ON pir_readings
    FOR EACH ROW
BEGIN
    IF NEW.is_suspicious = TRUE OR NEW.is_authorized_time = FALSE THEN
        INSERT INTO alerts (
            device_id, 
            alert_type, 
            severity, 
            title, 
            message, 
            source_table, 
            source_id, 
            triggered_at
        ) VALUES (
            NEW.device_id,
            'motion',
            CASE 
                WHEN NEW.is_suspicious = TRUE THEN 'high'
                WHEN NEW.is_authorized_time = FALSE THEN 'medium'
                ELSE 'low'
            END,
            CONCAT('Motion Alert - ', NEW.motion_type),
            CONCAT('Motion detected with intensity ', NEW.motion_intensity, ' for ', NEW.duration_seconds, ' seconds in zone ', COALESCE(NEW.detection_zone, 'unknown')),
            'pir_readings',
            NEW.id,
            NEW.recorded_at
        );
    END IF;
END$$

-- Trigger untuk door access alerts
CREATE TRIGGER tr_create_door_alert
    AFTER INSERT ON door_readings
    FOR EACH ROW
BEGIN
    IF NEW.is_forced_entry = TRUE OR NEW.is_authorized_access = FALSE THEN
        INSERT INTO alerts (
            device_id, 
            alert_type, 
            severity, 
            title, 
            message, 
            source_table, 
            source_id, 
            triggered_at
        ) VALUES (
            NEW.device_id,
            'door_access',
            CASE 
                WHEN NEW.is_forced_entry = TRUE THEN 'critical'
                WHEN NEW.is_authorized_access = FALSE THEN 'high'
                ELSE 'medium'
            END,
            CONCAT('Door Access Alert - ', NEW.access_type),
            CONCAT('Door ', NEW.door_location, ' accessed via ', NEW.access_type, ' for ', NEW.open_duration_seconds, ' seconds'),
            'door_readings',
            NEW.id,
            NEW.recorded_at
        );
    END IF;
END$$

DELIMITER ;

-- =====================================================
-- VIEWS FOR EASY DATA ACCESS
-- =====================================================

-- View untuk dashboard summary
CREATE VIEW dashboard_summary AS
SELECT 
    (SELECT COUNT(*) FROM devices WHERE is_online = TRUE) as online_devices,
    (SELECT COUNT(*) FROM devices WHERE is_online = FALSE) as offline_devices,
    (SELECT COUNT(*) FROM alerts WHERE is_acknowledged = FALSE AND triggered_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as unacknowledged_alerts_24h,
    (SELECT COUNT(*) FROM pir_readings WHERE motion_detected = TRUE AND recorded_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as motions_24h,
    (SELECT COUNT(*) FROM door_readings WHERE door_open = TRUE AND recorded_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as door_accesses_24h,
    (SELECT COUNT(*) FROM vibration_readings WHERE is_abnormal = TRUE AND recorded_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as abnormal_vibrations_24h;

-- View untuk device status
CREATE VIEW device_status AS
SELECT 
    d.id,
    d.device_id,
    d.name,
    d.type,
    d.location,
    d.battery_level,
    d.signal_strength,
    d.is_online,
    d.last_seen_at,
    COUNT(s.id) as sensor_count,
    COUNT(CASE WHEN s.is_active = TRUE THEN 1 END) as active_sensors,
    (SELECT COUNT(*) FROM alerts WHERE device_id = d.id AND is_acknowledged = FALSE) as unacknowledged_alerts
FROM devices d
LEFT JOIN sensors s ON d.id = s.device_id
GROUP BY d.id;

-- View untuk recent activities
CREATE VIEW recent_activities AS
SELECT 
    'motion' as activity_type,
    d.device_id,
    d.name as device_name,
    d.location,
    CONCAT('Motion detected - Intensity: ', p.motion_intensity, ', Duration: ', p.duration_seconds, 's') as description,
    p.recorded_at as activity_time
FROM pir_readings p
JOIN devices d ON p.device_id = d.id
WHERE p.motion_detected = TRUE AND p.recorded_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)

UNION ALL

SELECT 
    'door_access' as activity_type,
    d.device_id,
    d.name as device_name,
    d.location,
    CONCAT('Door ', dr.door_location, ' - ', dr.access_type, ' access for ', dr.open_duration_seconds, 's') as description,
    dr.recorded_at as activity_time
FROM door_readings dr
JOIN devices d ON dr.device_id = d.id
WHERE dr.door_open = TRUE AND dr.recorded_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)

UNION ALL

SELECT 
    'vibration' as activity_type,
    d.device_id,
    d.name as device_name,
    d.location,
    CONCAT('Vibration - Magnitude: ', ROUND(v.magnitude, 2), ' (', v.status, ')') as description,
    v.recorded_at as activity_time
FROM vibration_readings v
JOIN devices d ON v.device_id = d.id
WHERE v.is_abnormal = TRUE AND v.recorded_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)

ORDER BY activity_time DESC
LIMIT 50;

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

-- Procedure untuk cleanup old data
DELIMITER $$
CREATE PROCEDURE CleanupOldData(IN days_to_keep INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Delete old sensor readings (keep last X days)
    DELETE FROM sensor_readings WHERE reading_time < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
    DELETE FROM pir_readings WHERE recorded_at < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
    DELETE FROM door_readings WHERE recorded_at < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
    DELETE FROM vibration_readings WHERE recorded_at < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
    DELETE FROM door_access_readings WHERE recorded_at < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
    
    -- Delete old LoRa messages (keep last 30 days max)
    DELETE FROM lora_messages WHERE received_at < DATE_SUB(NOW(), INTERVAL LEAST(days_to_keep, 30) DAY);
    
    -- Delete old acknowledged alerts (keep last 90 days max)
    DELETE FROM alerts WHERE is_acknowledged = TRUE AND acknowledged_at < DATE_SUB(NOW(), INTERVAL LEAST(days_to_keep, 90) DAY);
    
    -- Delete old activity logs (keep last 180 days max)
    DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL LEAST(days_to_keep, 180) DAY);
    
    COMMIT;
    
    SELECT CONCAT('Cleanup completed. Removed data older than ', days_to_keep, ' days.') as result;
END$$

-- Procedure untuk device health check
CREATE PROCEDURE DeviceHealthCheck()
BEGIN
    SELECT 
        d.device_id,
        d.name,
        d.battery_level,
        d.signal_strength,
        d.is_online,
        d.last_seen_at,
        CASE 
            WHEN d.last_seen_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE) THEN 'OFFLINE'
            WHEN d.battery_level < 20 THEN 'LOW_BATTERY'
            WHEN d.signal_strength < -100 THEN 'WEAK_SIGNAL'
            ELSE 'HEALTHY'
        END as health_status,
        TIMESTAMPDIFF(MINUTE, d.last_seen_at, NOW()) as minutes_since_last_seen
    FROM devices d
    ORDER BY 
        CASE 
            WHEN d.last_seen_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE) THEN 1
            WHEN d.battery_level < 20 THEN 2
            WHEN d.signal_strength < -100 THEN 3
            ELSE 4
        END,
        d.device_id;
END$$

DELIMITER ;

-- =====================================================
-- INDEXES FOR PERFORMANCE OPTIMIZATION
-- =====================================================

-- Additional composite indexes for common queries
CREATE INDEX idx_pir_device_time_motion ON pir_readings(device_id, recorded_at, motion_detected);
CREATE INDEX idx_door_device_time_open ON door_readings(device_id, recorded_at, door_open);
CREATE INDEX idx_vibration_device_time_abnormal ON vibration_readings(device_id, recorded_at, is_abnormal);
CREATE INDEX idx_alerts_device_time_ack ON alerts(device_id, triggered_at, is_acknowledged);

-- Indexes for time-based queries
CREATE INDEX idx_pir_recorded_at_desc ON pir_readings(recorded_at DESC);
CREATE INDEX idx_door_recorded_at_desc ON door_readings(recorded_at DESC);
CREATE INDEX idx_vibration_recorded_at_desc ON vibration_readings(recorded_at DESC);
CREATE INDEX idx_alerts_triggered_at_desc ON alerts(triggered_at DESC);

-- =====================================================
-- COMPLETION MESSAGE
-- =====================================================
SELECT '✅ Smart Rack Security Database Schema Created Successfully!' as status,
       'Database: smart_rack_security' as database_name,
       '12 Tables Created' as tables_count,
       '3 Views Created' as views_count,
       '2 Stored Procedures Created' as procedures_count,
       '6 Triggers Created' as triggers_count;