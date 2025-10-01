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
