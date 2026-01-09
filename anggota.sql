-- ==========================================
-- ANGGOTA (MEMBERS) DATABASE SCHEMA
-- Yayasan Y-IBBI CMS
-- File: anggota.sql
-- ==========================================

-- ==========================================
-- Table: member_categories
-- Kategori/Tipe Anggota (Pimpinan, Pengurus Inti, dll)
-- ==========================================
CREATE TABLE IF NOT EXISTS `member_categories` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL COMMENT 'Nama kategori (Pimpinan Yayasan, Pengurus Inti, dll)',
    `slug` VARCHAR(100) NOT NULL COMMENT 'Slug untuk URL',
    `description` TEXT DEFAULT NULL COMMENT 'Deskripsi kategori',
    `display_order` INT(11) DEFAULT 0 COMMENT 'Urutan tampilan',
    `is_featured` TINYINT(1) DEFAULT 0 COMMENT 'Apakah ditampilkan sebagai featured (pimpinan)',
    `is_active` TINYINT(1) DEFAULT 1 COMMENT 'Status aktif',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_category_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- Table: members
-- Data Anggota/Pengurus Yayasan
-- ==========================================
CREATE TABLE IF NOT EXISTS `members` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id` INT(11) UNSIGNED DEFAULT NULL COMMENT 'FK ke member_categories',
    `name` VARCHAR(255) NOT NULL COMMENT 'Nama lengkap anggota',
    `position` VARCHAR(150) NOT NULL COMMENT 'Jabatan (Ketua, Wakil Ketua, Sekretaris, dll)',
    `photo` VARCHAR(500) DEFAULT NULL COMMENT 'Path foto anggota',
    `bio` TEXT DEFAULT NULL COMMENT 'Biografi singkat',
    `email` VARCHAR(255) DEFAULT NULL COMMENT 'Email anggota',
    `phone` VARCHAR(50) DEFAULT NULL COMMENT 'Nomor telepon',
    `whatsapp` VARCHAR(50) DEFAULT NULL COMMENT 'Nomor WhatsApp',
    `linkedin_url` VARCHAR(500) DEFAULT NULL COMMENT 'URL LinkedIn',
    `facebook_url` VARCHAR(500) DEFAULT NULL COMMENT 'URL Facebook',
    `instagram_url` VARCHAR(500) DEFAULT NULL COMMENT 'URL Instagram',
    `twitter_url` VARCHAR(500) DEFAULT NULL COMMENT 'URL Twitter/X',
    `display_order` INT(11) DEFAULT 0 COMMENT 'Urutan tampilan dalam kategori',
    `is_featured` TINYINT(1) DEFAULT 0 COMMENT 'Tampilkan sebagai pimpinan (card besar)',
    `is_active` TINYINT(1) DEFAULT 1 COMMENT 'Status aktif',
    `joined_date` DATE DEFAULT NULL COMMENT 'Tanggal bergabung',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_member_category` (`category_id`),
    KEY `idx_member_active` (`is_active`),
    KEY `idx_member_featured` (`is_featured`),
    KEY `idx_member_order` (`display_order`),
    CONSTRAINT `fk_member_category` FOREIGN KEY (`category_id`) 
        REFERENCES `member_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- Table: member_stats
-- Statistik yang ditampilkan di halaman anggota
-- ==========================================
CREATE TABLE IF NOT EXISTS `member_stats` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `icon` VARCHAR(100) NOT NULL COMMENT 'Font Awesome icon class (fas fa-users)',
    `number` VARCHAR(50) NOT NULL COMMENT 'Angka statistik (25+, 10, 50+)',
    `label` VARCHAR(100) NOT NULL COMMENT 'Label statistik (Anggota Aktif, Tahun Pengalaman)',
    `display_order` INT(11) DEFAULT 0 COMMENT 'Urutan tampilan',
    `is_active` TINYINT(1) DEFAULT 1 COMMENT 'Status aktif',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_stat_order` (`display_order`),
    KEY `idx_stat_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- Table: member_page_settings
-- Pengaturan halaman anggota (judul, deskripsi, CTA)
-- ==========================================
CREATE TABLE IF NOT EXISTS `member_page_settings` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL COMMENT 'Key pengaturan',
    `setting_value` TEXT DEFAULT NULL COMMENT 'Nilai pengaturan',
    `setting_type` ENUM('text', 'textarea', 'image', 'url') DEFAULT 'text',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================
-- INSERT DEFAULT DATA
-- ==========================================

-- Insert kategori default
INSERT INTO `member_categories` (`name`, `slug`, `description`, `display_order`, `is_featured`, `is_active`) VALUES
('Pimpinan Yayasan', 'pimpinan', 'Pimpinan utama yayasan', 1, 1, 1),
('Pengurus Inti', 'pengurus-inti', 'Pengurus inti organisasi yayasan', 2, 0, 1),
('Dewan Pembina', 'dewan-pembina', 'Dewan pembina yayasan', 3, 0, 1),
('Dewan Pengawas', 'dewan-pengawas', 'Dewan pengawas yayasan', 4, 0, 1);

-- Insert anggota contoh berdasarkan anggota.html
INSERT INTO `members` (`category_id`, `name`, `position`, `photo`, `bio`, `email`, `whatsapp`, `linkedin_url`, `display_order`, `is_featured`, `is_active`) VALUES
-- Pimpinan (Featured)
(1, 'H. Ahmad Syafii, M.Ag', 'Ketua Yayasan', 'uploads/members/ketua.jpg', 'Berpengalaman lebih dari 20 tahun dalam pengembangan pendidikan Islam dan pemberdayaan masyarakat. Beliau mendedikasikan hidupnya untuk memajukan umat melalui pendidikan dan dakwah.', 'ketua@yayasan.org', '6281234567890', 'https://linkedin.com/in/ahmadsyafii', 1, 1, 1),

-- Pengurus Inti
(2, 'Ustaz Ridwan Hakim', 'Wakil Ketua', 'uploads/members/wakil-ketua.jpg', NULL, 'wakil@yayasan.org', NULL, 'https://linkedin.com/in/ridwanhakim', 1, 0, 1),
(2, 'Siti Aminah, S.Pd', 'Sekretaris', 'uploads/members/sekretaris.jpg', NULL, 'sekretaris@yayasan.org', NULL, 'https://linkedin.com/in/sitiaminah', 2, 0, 1),
(2, 'H. Bambang Suryadi', 'Bendahara', 'uploads/members/bendahara.jpg', NULL, 'bendahara@yayasan.org', NULL, 'https://linkedin.com/in/bambangsuryadi', 3, 0, 1),
(2, 'Fatimah Azzahra, M.Si', 'Koordinator Program', 'uploads/members/koordinator-program.jpg', NULL, 'program@yayasan.org', NULL, 'https://linkedin.com/in/fatimahazzahra', 4, 0, 1),
(2, 'Ir. Muhammad Fadli', 'Hubungan Masyarakat', 'uploads/members/humas.jpg', NULL, 'humas@yayasan.org', NULL, 'https://linkedin.com/in/muhammadfadli', 5, 0, 1),
(2, 'Nurul Hidayah, M.Pd', 'Koordinator Pendidikan', 'uploads/members/koordinator-pendidikan.jpg', NULL, 'pendidikan@yayasan.org', NULL, 'https://linkedin.com/in/nurulhidayah', 6, 0, 1);

-- Insert statistik
INSERT INTO `member_stats` (`icon`, `number`, `label`, `display_order`, `is_active`) VALUES
('fas fa-users', '25+', 'Anggota Aktif', 1, 1),
('fas fa-calendar-check', '10', 'Tahun Pengalaman', 2, 1),
('fas fa-hand-holding-heart', '50+', 'Program Terlaksana', 3, 1),
('fas fa-mosque', '15', 'Cabang Daerah', 4, 1);

-- Insert pengaturan halaman
INSERT INTO `member_page_settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('page_badge', 'Tim Kami', 'text'),
('page_title', 'Anggota Yayasan', 'text'),
('page_description', 'Kenali orang-orang hebat di balik setiap program dan kegiatan yayasan kami yang berdedikasi untuk kebaikan umat.', 'textarea'),
('leadership_title', 'Pimpinan Yayasan', 'text'),
('team_title', 'Pengurus Inti', 'text'),
('cta_title', 'Bergabung Bersama Kami', 'text'),
('cta_description', 'Jadilah bagian dari keluarga besar yayasan dan berkontribusi dalam kebaikan untuk umat dan masyarakat.', 'textarea'),
('cta_button_text', 'Hubungi Kami', 'text'),
('cta_button_url', 'index.php?page=contact', 'url');


-- ==========================================
-- INDEXES FOR BETTER QUERY PERFORMANCE
-- ==========================================
-- Additional indexes are already defined in table creation

-- ==========================================
-- END OF ANGGOTA DATABASE SCHEMA
-- ==========================================
