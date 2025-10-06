-- backend/schema.sql
CREATE DATABASE IF NOT EXISTS parcel_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE parcel_db;

CREATE TABLE IF NOT EXISTS shipments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tracking_number VARCHAR(32) NOT NULL UNIQUE,
  sender_name VARCHAR(120) NOT NULL,
  receiver_name VARCHAR(120) NOT NULL,
  origin VARCHAR(120) NOT NULL,
  destination VARCHAR(120) NOT NULL,
  weight DECIMAL(10,2) NOT NULL DEFAULT 0,
  price DECIMAL(10,2) NULL,
  status ENUM('Booked','In Transit','Delivered','Cancelled') NOT NULL DEFAULT 'Booked',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- New: admin_profile table to store admin profile details (keyed by admin_id)
CREATE TABLE IF NOT EXISTS admin_profile (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NULL,
  phone VARCHAR(30) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_admin (admin_id),
  UNIQUE KEY uniq_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional: seed via PHP to avoid storing raw hashes in SQL

-- New: bookings table to log booking requests (mirrors public booking form)
CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tracking_number VARCHAR(32) NULL,
  sender_name VARCHAR(120) NOT NULL,
  receiver_name VARCHAR(120) NOT NULL,
  origin VARCHAR(120) NOT NULL,
  destination VARCHAR(120) NOT NULL,
  weight DECIMAL(10,2) NOT NULL DEFAULT 0,
  price DECIMAL(10,2) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (tracking_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- New: customer table to store registered customer details (form-driven)
CREATE TABLE IF NOT EXISTS customer (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  address VARCHAR(255) NOT NULL,
  district VARCHAR(80) NOT NULL,
  province VARCHAR(80) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- New: admin_messages table (stores messages to customers)
-- Includes: customer_id, customer_name, customer_email, subject, message body
CREATE TABLE IF NOT EXISTS admin_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  customer_name VARCHAR(120) NOT NULL,
  customer_email VARCHAR(150) NOT NULL,
  subject VARCHAR(200) NOT NULL,
  body MEDIUMTEXT NOT NULL,
  delivery_status ENUM('queued','sent','failed') NOT NULL DEFAULT 'queued',
  admin_message TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  sent_at TIMESTAMP NULL DEFAULT NULL,
  INDEX idx_admin_messages_customer (customer_id),
  INDEX idx_admin_messages_status_created (delivery_status, created_at),
  CONSTRAINT fk_admin_msg_customer FOREIGN KEY (customer_id) REFERENCES customer(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



