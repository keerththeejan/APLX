-- backend/schema.sql
CREATE DATABASE IF NOT EXISTS parcel_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE parcel_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','staff','customer') NOT NULL DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- New: customers table to link user accounts that are customers
CREATE TABLE IF NOT EXISTS customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_customer_user (user_id),
  CONSTRAINT fk_customers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

-- New: user_profiles table to store additional admin/staff profile details
CREATE TABLE IF NOT EXISTS user_profiles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  phone VARCHAR(30) NULL,
  company VARCHAR(120) NULL,
  address VARCHAR(255) NULL,
  city VARCHAR(80) NULL,
  state VARCHAR(80) NULL,
  country VARCHAR(80) NULL,
  pincode VARCHAR(20) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user (user_id),
  CONSTRAINT fk_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
