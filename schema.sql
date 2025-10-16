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

-- New: users table (generic users incl. role 'customer' or 'admin')
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(32) NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_email (email)
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



  -- Services cards shown on Home (first 4 by sort_order)
  CREATE TABLE IF NOT EXISTS `services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `image_url` VARCHAR(512) NOT NULL,
    `title` VARCHAR(120) NOT NULL,
    `description` VARCHAR(500) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_services_sort` (`sort_order`,`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Auto-scrolling transport gallery on Home
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `image_url` VARCHAR(512) NOT NULL,
  `tag` VARCHAR(60) DEFAULT NULL,
  `day` TINYINT UNSIGNED DEFAULT NULL,
  `month` VARCHAR(12) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_gallery_sort` (`sort_order`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional starter rows (uncomment to seed)
-- ('✈️', NULL, 'Air Freight', 'Efficient and reliable air solutions.', 1),
-- ('🛳️', NULL, 'Ocean Freight', 'Global ocean freight services.', 2),
-- ('🚚', NULL, 'Land Transport', 'Fast road transport solutions.', 3),
-- ('🏬', NULL, 'Warehousing', 'Secure storage & inventory.', 4);

  -- INSERT INTO gallery(image_url, tag, day, month, sort_order) VALUES
  -- ('/APLX/uploads/gallery/sample1.jpg', 'Transport', 25, 'Dec', 1),
  -- ('/APLX/uploads/gallery/sample2.jpg', 'Transport', 30, 'Dec', 2);

-- Site contact details (single row). Admin editable, used in footer and contact cards
CREATE TABLE IF NOT EXISTS `site_contact` (
  `id` TINYINT UNSIGNED NOT NULL DEFAULT 1 PRIMARY KEY,
  `address` VARCHAR(255) NOT NULL DEFAULT '',
  `phone` VARCHAR(80) NOT NULL DEFAULT '',
  `email` VARCHAR(150) NOT NULL DEFAULT '',
  `hours_weekday` VARCHAR(120) NOT NULL DEFAULT 'Mon - Fri: 8:30 AM - 4:15 PM',
  `hours_sat` VARCHAR(120) NOT NULL DEFAULT 'Sat: 9:00 AM - 2:00 PM',
  `hours_sun` VARCHAR(120) NOT NULL DEFAULT 'Sun: Closed',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default row if missing
INSERT INTO site_contact (id, address, phone, email)
SELECT 1, 'Ariviyal Nagar, Kilinochchi, Sri Lanka', '+94 21 492 7799', 'info@slgti.com'
WHERE NOT EXISTS (SELECT 1 FROM site_contact WHERE id=1);
