-- =====================================================
-- MIGRATION SCRIPT: Tambah kolom baru ke campaign_programs
-- Jalankan script ini jika database sudah ada
-- =====================================================

-- Tambah kolom description
ALTER TABLE `campaign_programs` 
ADD COLUMN `description` TEXT DEFAULT NULL AFTER `title`;

-- Tambah kolom beneficiaries_count
ALTER TABLE `campaign_programs` 
ADD COLUMN `beneficiaries_count` INT DEFAULT 0 AFTER `goal_amount`;

-- Tambah kolom donators_count  
ALTER TABLE `campaign_programs` 
ADD COLUMN `donators_count` INT DEFAULT 0 AFTER `beneficiaries_count`;

-- Tambah kolom gallery_images
ALTER TABLE `campaign_programs` 
ADD COLUMN `gallery_images` TEXT DEFAULT NULL AFTER `donators_count`;

-- Update data existing dengan deskripsi dan jumlah penerima/donatur
UPDATE `campaign_programs` SET 
    `description` = 'Program distribusi makanan untuk membantu masyarakat kurang mampu mendapatkan makanan bergizi.',
    `beneficiaries_count` = 250,
    `donators_count` = 120
WHERE `id` = 1;

UPDATE `campaign_programs` SET 
    `description` = 'Program beasiswa pendidikan untuk anak-anak yatim dan dhuafa agar dapat melanjutkan pendidikan.',
    `beneficiaries_count` = 150,
    `donators_count` = 85
WHERE `id` = 2;

UPDATE `campaign_programs` SET 
    `description` = 'Program renovasi dan pembangunan masjid serta musholla di daerah yang membutuhkan.',
    `beneficiaries_count` = 500,
    `donators_count` = 200
WHERE `id` = 3;

UPDATE `campaign_programs` SET 
    `description` = 'Program layanan kesehatan gratis untuk masyarakat kurang mampu.',
    `beneficiaries_count` = 300,
    `donators_count` = 95
WHERE `id` = 4;
