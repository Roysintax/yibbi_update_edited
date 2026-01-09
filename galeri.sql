-- ==========================================
-- GALERI (GALLERY) DATABASE SCHEMA
-- Yayasan Y-IBBI CMS
-- File: galeri.sql
-- ==========================================

-- ==========================================
-- Table: gallery_categories
-- Kategori galeri (Pendidikan, Sosial, Keagamaan, Kesehatan)
-- ==========================================
CREATE TABLE IF NOT EXISTS `gallery_categories` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL COMMENT 'Nama kategori',
    `slug` VARCHAR(100) NOT NULL COMMENT 'Slug untuk filter',
    `icon` VARCHAR(100) DEFAULT NULL COMMENT 'Font Awesome icon class',
    `display_order` INT(11) DEFAULT 0 COMMENT 'Urutan tampilan',
    `is_active` TINYINT(1) DEFAULT 1 COMMENT 'Status aktif',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_category_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- Table: gallery_items
-- Data foto/item galeri
-- ==========================================
CREATE TABLE IF NOT EXISTS `gallery_items` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id` INT(11) UNSIGNED DEFAULT NULL COMMENT 'FK ke gallery_categories',
    `title` VARCHAR(255) NOT NULL COMMENT 'Judul/nama kegiatan',
    `description` TEXT DEFAULT NULL COMMENT 'Deskripsi foto',
    `image` VARCHAR(500) NOT NULL COMMENT 'Path gambar',
    `event_date` DATE DEFAULT NULL COMMENT 'Tanggal kegiatan',
    `location` VARCHAR(255) DEFAULT NULL COMMENT 'Lokasi kegiatan',
    `display_order` INT(11) DEFAULT 0 COMMENT 'Urutan tampilan',
    `is_featured` TINYINT(1) DEFAULT 0 COMMENT 'Tampilkan sebagai featured',
    `is_active` TINYINT(1) DEFAULT 1 COMMENT 'Status aktif',
    `views` INT(11) DEFAULT 0 COMMENT 'Jumlah dilihat',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_gallery_category` (`category_id`),
    KEY `idx_gallery_active` (`is_active`),
    KEY `idx_gallery_featured` (`is_featured`),
    KEY `idx_gallery_date` (`event_date`),
    CONSTRAINT `fk_gallery_category` FOREIGN KEY (`category_id`) 
        REFERENCES `gallery_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- Table: gallery_settings
-- Pengaturan halaman galeri
-- ==========================================
CREATE TABLE IF NOT EXISTS `gallery_settings` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL COMMENT 'Key pengaturan',
    `setting_value` TEXT DEFAULT NULL COMMENT 'Nilai pengaturan',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================
-- INSERT DEFAULT DATA
-- ==========================================

-- Insert kategori default
INSERT INTO `gallery_categories` (`name`, `slug`, `icon`, `display_order`, `is_active`) VALUES
('Pendidikan', 'pendidikan', 'fas fa-graduation-cap', 1, 1),
('Sosial', 'sosial', 'fas fa-hands-helping', 2, 1),
('Keagamaan', 'keagamaan', 'fas fa-mosque', 3, 1),
('Kesehatan', 'kesehatan', 'fas fa-heartbeat', 4, 1);

-- Insert sample gallery items
INSERT INTO `gallery_items` (`category_id`, `title`, `description`, `image`, `event_date`, `location`, `display_order`, `is_active`) VALUES
-- Pendidikan
(1, 'Pelatihan Guru TPQ', 'Workshop peningkatan kompetensi guru TPQ se-Kota Bandung', 'uploads/gallery/pelatihan-guru.jpg', '2024-12-15', 'Bandung', 1, 1),
(1, 'Penyerahan Beasiswa', 'Pemberian beasiswa kepada siswa berprestasi dari keluarga kurang mampu', 'uploads/gallery/beasiswa.jpg', '2024-12-01', 'Jakarta', 2, 1),
(1, 'Wisuda Tahfidz Quran', 'Pelepasan santri penghafal 30 juz Al-Quran angkatan ke-5', 'uploads/gallery/wisuda-tahfidz.jpg', '2024-11-15', 'Surabaya', 3, 1),

-- Sosial
(2, 'Pembagian Sembako', 'Distribusi paket sembako untuk warga terdampak bencana', 'uploads/gallery/sembako.jpg', '2024-12-10', 'Bogor', 1, 1),
(2, 'Santunan Yatim Piatu', 'Penyaluran dana santunan untuk 100 anak yatim piatu', 'uploads/gallery/santunan-yatim.jpg', '2024-11-28', 'Depok', 2, 1),
(2, 'Aksi Peduli Bencana', 'Penggalangan dana dan bantuan untuk korban banjir', 'uploads/gallery/peduli-bencana.jpg', '2024-11-10', 'Bekasi', 3, 1),

-- Keagamaan
(3, 'Kajian Rutin Mingguan', 'Kajian tafsir Al-Quran setiap Ahad pagi', 'uploads/gallery/kajian.jpg', '2024-12-08', 'Masjid Al-Ikhlas', 1, 1),
(3, 'Buka Puasa Bersama', 'Iftar jamaah bersama 500 warga', 'uploads/gallery/buka-puasa.jpg', '2024-11-25', 'Jakarta', 2, 1),
(3, 'Tabligh Akbar', 'Ceramah akbar memperingati Maulid Nabi', 'uploads/gallery/tabligh.jpg', '2024-11-05', 'Tangerang', 3, 1),

-- Kesehatan
(4, 'Pengobatan Gratis', 'Layanan kesehatan gratis untuk masyarakat', 'uploads/gallery/pengobatan.jpg', '2024-12-05', 'Cianjur', 1, 1),
(4, 'Aksi Donor Darah', 'Kegiatan donor darah bersama PMI', 'uploads/gallery/donor-darah.jpg', '2024-11-20', 'Bandung', 2, 1),
(4, 'Khitan Massal Gratis', 'Program khitan gratis untuk 50 anak', 'uploads/gallery/khitan.jpg', '2024-11-01', 'Sukabumi', 3, 1);

-- Insert pengaturan halaman
INSERT INTO `gallery_settings` (`setting_key`, `setting_value`) VALUES
('page_badge', 'Dokumentasi'),
('page_title', 'Galeri Kegiatan'),
('page_description', 'Kumpulan momen berharga dari berbagai kegiatan dan program yayasan yang telah kami laksanakan bersama masyarakat.'),
('stat_photos', '150+'),
('stat_photos_label', 'Dokumentasi Foto'),
('stat_events', '50+'),
('stat_events_label', 'Kegiatan Terdokumentasi'),
('stat_categories', '4'),
('stat_categories_label', 'Kategori Kegiatan'),
('stat_year', '2024'),
('stat_year_label', 'Tahun Aktif');


-- ==========================================
-- END OF GALERI DATABASE SCHEMA
-- ==========================================
