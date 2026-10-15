CREATE DATABASE IF NOT EXISTS pesez_farm_db;
USE pesez_farm_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'worker') NOT NULL DEFAULT 'worker',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS flocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_name VARCHAR(100) NOT NULL,
    bird_type ENUM('Layer', 'Broiler') NOT NULL,
    initial_count INT NOT NULL,
    current_count INT NOT NULL,
    arrival_date DATE NOT NULL,
    status ENUM('active', 'sold') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS egg_production (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flock_id INT NOT NULL,
    collection_date DATE NOT NULL,
    quantity_good INT NOT NULL DEFAULT 0,
    quantity_damaged INT NOT NULL DEFAULT 0,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (flock_id) REFERENCES flocks(id) ON DELETE RESTRICT,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS feed_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    feed_type VARCHAR(100) NOT NULL UNIQUE,
    quantity_in_stock_kg DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS feed_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flock_id INT NOT NULL,
    usage_date DATE NOT NULL,
    feed_type VARCHAR(100) NOT NULL,
    quantity_used_kg DECIMAL(10,2) NOT NULL,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (flock_id) REFERENCES flocks(id) ON DELETE RESTRICT,
    FOREIGN KEY (feed_type) REFERENCES feed_inventory(feed_type) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS mortality (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flock_id INT NOT NULL,
    record_date DATE NOT NULL,
    number_of_birds INT NOT NULL,
    cause_of_death VARCHAR(255),
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (flock_id) REFERENCES flocks(id) ON DELETE RESTRICT,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS health_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flock_id INT NOT NULL,
    record_date DATE NOT NULL,
    type ENUM('Vaccination', 'Treatment') NOT NULL,
    medication_name VARCHAR(150) NOT NULL,
    notes TEXT,
    next_due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (flock_id) REFERENCES flocks(id) ON DELETE RESTRICT
);

-- Insert a default admin user (Password is 'admin123')
INSERT IGNORE INTO users (username, password_hash, role) VALUES 
('admin', '$2y$10$ndoNFuGHW1V9mdo0t/aw.O6UGkLpwxwny.7SURQ7qQuBuCjSzrjtq', 'admin');
