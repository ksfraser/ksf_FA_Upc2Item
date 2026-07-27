-- ksf_FA_Upc2Item Module Schema
-- Uses @TB_PREF@ for table prefix

-- Scan sessions
CREATE TABLE IF NOT EXISTS `0_ksf_upc2item_scans` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `source_type` ENUM('live','csv','image') NOT NULL DEFAULT 'live',
    `filename` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Scanned items (UPC lookups)
CREATE TABLE IF NOT EXISTS `0_ksf_upc2item_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `scan_id` INT(11) NOT NULL,
    `upc` VARCHAR(64) NOT NULL,
    `title` VARCHAR(512) DEFAULT NULL,
    `description` TEXT,
    `image_url` VARCHAR(1024) DEFAULT NULL,
    `category` VARCHAR(255) DEFAULT NULL,
    `brand` VARCHAR(255) DEFAULT NULL,
    `model` VARCHAR(255) DEFAULT NULL,
    `amazon_price` DECIMAL(12,2) DEFAULT NULL,
    `amazon_retail` DECIMAL(12,2) DEFAULT NULL,
    `ebay_price` DECIMAL(12,2) DEFAULT NULL,
    `ebay_retail` DECIMAL(12,2) DEFAULT NULL,
    `facebook_price` DECIMAL(12,2) DEFAULT NULL,
    `facebook_retail` DECIMAL(12,2) DEFAULT NULL,
    `fa_stock_id` VARCHAR(30) DEFAULT NULL,
    `fa_imported` TINYINT(1) NOT NULL DEFAULT 0,
    `fa_imported_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `scan_id` (`scan_id`),
    KEY `upc` (`upc`),
    KEY `fa_stock_id` (`fa_stock_id`),
    CONSTRAINT `fk_upc2item_scans` FOREIGN KEY (`scan_id`) REFERENCES `0_ksf_upc2item_scans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Price book mappings (source → FA sales_type)
CREATE TABLE IF NOT EXISTS `0_ksf_upc2item_pricebook_map` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `source_name` VARCHAR(64) NOT NULL,
    `fa_sales_type_id` INT(11) NOT NULL,
    `enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `source_name` (`source_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default mappings
INSERT INTO `0_ksf_upc2item_pricebook_map` (`source_name`, `fa_sales_type_id`, `enabled`)
VALUES ('Retail', 1, 1), ('Amazon', 1, 1), ('Ebay', 1, 1), ('Facebook', 1, 1)
ON DUPLICATE KEY UPDATE `fa_sales_type_id` = VALUES(`fa_sales_type_id`);

-- Module version
INSERT INTO `fa_modules` (`name`, `version`, `enabled`, `installed`) VALUES ('UPC2Item', '2.4.3-0', 1, NOW()) ON DUPLICATE KEY UPDATE `version` = '2.4.3-0';
