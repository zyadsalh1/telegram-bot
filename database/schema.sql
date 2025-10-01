-- Core tables
CREATE TABLE IF NOT EXISTS settings (
  `key` varchar(100) NOT NULL PRIMARY KEY,
  `value` json NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS devices (
  device_id varchar(64) NOT NULL PRIMARY KEY,
  user_agent text NULL,
  ip varchar(64) NULL,
  deposit_required tinyint(1) NOT NULL DEFAULT 0,
  created_at datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS shipping_zones (
  id int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(120) NOT NULL,
  country varchar(2) NOT NULL,
  cities json NULL,
  delivery_fee decimal(10,2) NOT NULL DEFAULT 0,
  min_order decimal(10,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contact/Social settings grouped for admin
CREATE TABLE IF NOT EXISTS contact_settings (
  id tinyint unsigned NOT NULL PRIMARY KEY DEFAULT 1,
  phone varchar(40) NULL,
  email varchar(120) NULL,
  facebook varchar(255) NULL,
  instagram varchar(255) NULL,
  tiktok varchar(255) NULL,
  whatsapp varchar(255) NULL,
  updated_at datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- CMS pages with per-locale contents and SEO
CREATE TABLE IF NOT EXISTS pages (
  id int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  slug varchar(150) NOT NULL UNIQUE,
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS page_translations (
  id int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  page_id int unsigned NOT NULL,
  locale varchar(2) NOT NULL,
  title varchar(200) NOT NULL,
  content mediumtext NULL,
  meta_title varchar(255) NULL,
  meta_description varchar(255) NULL,
  meta_keywords varchar(255) NULL,
  UNIQUE KEY uniq_page_locale (page_id, locale),
  CONSTRAINT fk_pt_page FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SMTP settings
CREATE TABLE IF NOT EXISTS smtp_settings (
  id tinyint unsigned NOT NULL PRIMARY KEY DEFAULT 1,
  host varchar(150) NOT NULL,
  port smallint unsigned NOT NULL DEFAULT 587,
  username varchar(150) NULL,
  password varchar(200) NULL,
  encryption enum('none','ssl','tls') NOT NULL DEFAULT 'tls',
  from_email varchar(150) NULL,
  from_name varchar(150) NULL,
  updated_at datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Email templates per locale
CREATE TABLE IF NOT EXISTS email_templates (
  id int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `key` varchar(100) NOT NULL,
  locale varchar(2) NOT NULL,
  subject varchar(255) NOT NULL,
  body mediumtext NOT NULL,
  UNIQUE KEY uniq_key_locale (`key`, locale)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products and Orders
CREATE TABLE IF NOT EXISTS products (
  id int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name_ar varchar(255) NOT NULL,
  name_en varchar(255) NOT NULL,
  price decimal(10,2) NOT NULL,
  stock int NOT NULL DEFAULT 0,
  image varchar(255) NULL,
  active tinyint(1) NOT NULL DEFAULT 1,
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
  id int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  order_code varchar(20) NOT NULL UNIQUE,
  customer_name varchar(150) NOT NULL,
  customer_email varchar(150) NULL,
  customer_phone varchar(50) NULL,
  address text NULL,
  zone_id int unsigned NULL,
  subtotal decimal(10,2) NOT NULL DEFAULT 0,
  shipping decimal(10,2) NOT NULL DEFAULT 0,
  discount decimal(10,2) NOT NULL DEFAULT 0,
  deposit decimal(10,2) NOT NULL DEFAULT 0,
  total decimal(10,2) NOT NULL DEFAULT 0,
  status enum('pending','confirmed','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
  id int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  order_id int unsigned NOT NULL,
  product_id int unsigned NOT NULL,
  name varchar(255) NOT NULL,
  qty int NOT NULL,
  price decimal(10,2) NOT NULL,
  total decimal(10,2) NOT NULL,
  CONSTRAINT fk_oi_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
