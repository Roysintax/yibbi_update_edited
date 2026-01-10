-- ==========================================
-- KONFIRMASI DONASI DATABASE SCHEMA
-- Yayasan Y-IBBI CMS
-- File: konfirmasi.sql
-- ==========================================

-- ==========================================
-- Table: donations
-- Data donasi yang dikirim dari form
-- ==========================================
CREATE TABLE IF NOT EXISTS `donations` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `transaction_id` VARCHAR(50) NOT NULL COMMENT 'ID Transaksi unik (DON-YYYYMMDDXXXXX)',
    `program_id` INT(11) UNSIGNED DEFAULT NULL COMMENT 'FK ke campaign_programs (nullable untuk donasi umum)',
    `program_name` VARCHAR(255) DEFAULT 'Donasi Umum' COMMENT 'Nama program saat donasi',
    `donor_name` VARCHAR(255) NOT NULL COMMENT 'Nama donatur',
    `donor_email` VARCHAR(255) NOT NULL COMMENT 'Email donatur',
    `donor_phone` VARCHAR(20) NOT NULL COMMENT 'No HP/WhatsApp donatur',
    `amount` DECIMAL(15,2) NOT NULL COMMENT 'Nominal donasi',
    `message` TEXT DEFAULT NULL COMMENT 'Pesan/doa dari donatur',
    `payment_method` VARCHAR(100) DEFAULT 'Transfer Bank' COMMENT 'Metode pembayaran',
    `payment_proof` VARCHAR(500) DEFAULT NULL COMMENT 'Path file bukti pembayaran',
    `status` ENUM('pending', 'verified', 'rejected', 'completed') DEFAULT 'pending' COMMENT 'Status donasi',
    `admin_notes` TEXT DEFAULT NULL COMMENT 'Catatan dari admin',
    `verified_at` DATETIME DEFAULT NULL COMMENT 'Waktu verifikasi',
    `verified_by` INT(11) UNSIGNED DEFAULT NULL COMMENT 'ID admin yang memverifikasi',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu submit donasi',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_transaction_id` (`transaction_id`),
    KEY `idx_donation_status` (`status`),
    KEY `idx_donation_date` (`created_at`),
    KEY `idx_donor_email` (`donor_email`),
    KEY `idx_program` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================
-- Table: donation_settings
-- Pengaturan halaman donasi
-- ==========================================
CREATE TABLE IF NOT EXISTS `donation_settings` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================
-- Table: bank_accounts
-- Rekening bank untuk donasi
-- ==========================================
CREATE TABLE IF NOT EXISTS `bank_accounts` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `bank_name` VARCHAR(100) NOT NULL COMMENT 'Nama bank',
    `account_name` VARCHAR(255) NOT NULL COMMENT 'Nama pemilik rekening',
    `account_number` VARCHAR(50) NOT NULL COMMENT 'Nomor rekening',
    `bank_logo` VARCHAR(500) DEFAULT NULL COMMENT 'URL logo bank',
    `bank_class` VARCHAR(50) DEFAULT NULL COMMENT 'CSS class tambahan',
    `display_order` INT(11) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================
-- Table: ewallets
-- E-Wallet untuk donasi
-- ==========================================
CREATE TABLE IF NOT EXISTS `ewallets` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `wallet_name` VARCHAR(100) NOT NULL COMMENT 'Nama e-wallet (GoPay, OVO, DANA)',
    `wallet_number` VARCHAR(20) NOT NULL COMMENT 'Nomor e-wallet',
    `wallet_icon` VARCHAR(50) DEFAULT 'fas fa-wallet' COMMENT 'Icon class',
    `display_order` INT(11) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================
-- INSERT DEFAULT DATA
-- ==========================================

-- Bank accounts
INSERT INTO `bank_accounts` (`bank_name`, `account_name`, `account_number`, `bank_logo`, `bank_class`, `display_order`) VALUES
('Bank Central Asia (BCA)', 'Yayasan Y-ibbi', '1234567890', 'https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg', '', 1),
('Bank Mandiri', 'Yayasan Y-ibbi', '9876543210', 'https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg', 'mandiri', 2),
('Bank Syariah Indonesia (BSI)', 'Yayasan Y-ibbi', '7171234567', 'https://upload.wikimedia.org/wikipedia/commons/a/a0/Bank_Syariah_Indonesia.svg', 'bsi', 3);

-- E-Wallets
INSERT INTO `ewallets` (`wallet_name`, `wallet_number`, `wallet_icon`, `display_order`) VALUES
('GoPay', '081234567890', 'fas fa-wallet', 1),
('OVO', '081234567890', 'fas fa-wallet', 2),
('DANA', '081234567890', 'fas fa-wallet', 3);

-- Donation settings
INSERT INTO `donation_settings` (`setting_key`, `setting_value`) VALUES
('page_title', 'Donasi Sekarang'),
('page_description', 'Mari bersama-sama berbagi kebaikan. Setiap donasi Anda akan membantu program-program sosial keagamaan kami.'),
('verification_time', '1x24 jam'),
('min_donation', '10000'),
('confirmation_title', 'Terima Kasih!'),
('confirmation_subtitle', 'Donasi Anda sedang dalam proses verifikasi');


-- ==========================================
-- END OF KONFIRMASI DATABASE SCHEMA
-- ==========================================
