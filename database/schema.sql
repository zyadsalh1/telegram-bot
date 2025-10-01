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
