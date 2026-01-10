<?php
/**
 * Konfirmasi Donasi Page
 * Displays donation confirmation after form submission
 * Accessed via: index.php?page=konfirmasi&id=TRANSACTION_ID
 * 
 * Database tables used:
 * - donations
 * - donation_settings
 */

// ==========================================
// DATABASE CONNECTION (PDO)
// ==========================================
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=yayasan_cms;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $pdo = null;
}

// ==========================================
// HELPER FUNCTIONS
// ==========================================

/**
 * Get donation setting value by key
 */
function getDonationSetting(PDO $pdo, string $key, string $default = ''): string {
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM donation_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? htmlspecialchars($result) : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

/**
 * Get donation by transaction ID
 */
function getDonationByTransactionId(PDO $pdo, string $transactionId): ?array {
    try {
        $stmt = $pdo->prepare("
            SELECT d.*, cp.title as program_title 
            FROM donations d
            LEFT JOIN campaign_programs cp ON d.program_id = cp.id
            WHERE d.transaction_id = ?
        ");
        $stmt->execute([$transactionId]);
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Format currency to Rupiah
 */
function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Format datetime to Indonesian
 */
function formatDateTimeID(string $datetime): string {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $timestamp = strtotime($datetime);
    $day = date('j', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);
    $time = date('H:i', $timestamp);
    
    return "$day $month $year, $time WIB";
}

/**
 * Get status label and class
 */
function getStatusInfo(string $status): array {
    $statuses = [
        'pending' => ['label' => 'Menunggu Konfirmasi', 'class' => 'pending', 'icon' => 'fas fa-clock'],
        'verified' => ['label' => 'Terverifikasi', 'class' => 'verified', 'icon' => 'fas fa-check-circle'],
        'rejected' => ['label' => 'Ditolak', 'class' => 'rejected', 'icon' => 'fas fa-times-circle'],
        'completed' => ['label' => 'Selesai', 'class' => 'completed', 'icon' => 'fas fa-check-double']
    ];
    
    return $statuses[$status] ?? $statuses['pending'];
}

/**
 * Get timeline step class based on status
 */
function getTimelineClass(string $currentStatus, int $step): string {
    $statusOrder = ['pending' => 1, 'verified' => 2, 'completed' => 3];
    $currentStep = $statusOrder[$currentStatus] ?? 1;
    
    if ($step < $currentStep) return 'completed';
    if ($step == $currentStep) return 'active';
    return 'pending';
}

// ==========================================
// FETCH DATA
// ==========================================
$transactionId = $_GET['id'] ?? '';
$donation = null;
$settings = [];

if ($pdo && !empty($transactionId)) {
    $donation = getDonationByTransactionId($pdo, $transactionId);
    $settings = [
        'confirmation_title' => getDonationSetting($pdo, 'confirmation_title', 'Terima Kasih!'),
        'confirmation_subtitle' => getDonationSetting($pdo, 'confirmation_subtitle', 'Donasi Anda sedang dalam proses verifikasi'),
        'verification_time' => getDonationSetting($pdo, 'verification_time', '1x24 jam'),
    ];
}

// Get WhatsApp number from global site_settings
$admin_wa = isset($site_settings['whatsapp_number']) ? preg_replace('/[^0-9]/', '', $site_settings['whatsapp_number']) : '6281234567890';
if (empty($admin_wa)) $admin_wa = '6281234567890';

// Status info
$statusInfo = $donation ? getStatusInfo($donation['status']) : getStatusInfo('pending');
?>

<!-- Load Page Specific CSS -->
<link rel="stylesheet" href="template/konfirmasi.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<section class="konfirmasi-section">
    <div class="container">
        <?php if ($donation): ?>
        <!-- Header -->
        <div class="konfirmasi-header">
            <div class="success-icon <?= $statusInfo['class'] ?>">
                <i class="<?= $statusInfo['icon'] ?>"></i>
            </div>
            <h1><?= $settings['confirmation_title'] ?></h1>
            <p class="subtitle"><?= $settings['confirmation_subtitle'] ?></p>
        </div>

        <!-- Progress Timeline -->
        <div class="timeline-wrapper">
            <div class="timeline">
                <!-- Step 1: Pembayaran Selesai -->
                <div class="timeline-step <?= getTimelineClass($donation['status'], 1) ?>">
                    <div class="step-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="step-connector">
                        <div class="connector-line"></div>
                    </div>
                    <div class="step-content">
                        <h3>Pembayaran Selesai</h3>
                        <p>Bukti transfer telah diterima</p>
                    </div>
                </div>

                <!-- Step 2: Tunggu Konfirmasi -->
                <div class="timeline-step <?= getTimelineClass($donation['status'], 2) ?>">
                    <div class="step-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="step-connector">
                        <div class="connector-line"></div>
                    </div>
                    <div class="step-content">
                        <h3>Tunggu Konfirmasi Admin</h3>
                        <p>Sedang dalam proses verifikasi</p>
                    </div>
                </div>

                <!-- Step 3: Pembayaran Sukses -->
                <div class="timeline-step <?= getTimelineClass($donation['status'], 3) ?>">
                    <div class="step-icon">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="step-content">
                        <h3>Pembayaran Sukses</h3>
                        <p>Donasi berhasil dikonfirmasi</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="info-card">
            <div class="info-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="info-content">
                <h4>Proses Verifikasi</h4>
                <p>
                    Admin kami akan memverifikasi pembayaran Anda dalam waktu
                    <strong><?= $settings['verification_time'] ?></strong> pada hari kerja. Anda akan menerima
                    notifikasi melalui WhatsApp setelah pembayaran dikonfirmasi.
                </p>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="transaction-card">
            <h3 class="card-title">
                <i class="fas fa-receipt"></i>
                Detail Transaksi
            </h3>
            <div class="transaction-details">
                <div class="detail-row">
                    <span class="label">ID Transaksi</span>
                    <span class="value"><?= htmlspecialchars($donation['transaction_id']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Tanggal</span>
                    <span class="value"><?= formatDateTimeID($donation['created_at']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Nama Donatur</span>
                    <span class="value"><?= htmlspecialchars($donation['donor_name']) ?></span>
                </div>
                <?php if (!empty($donation['program_name']) && $donation['program_name'] !== 'Donasi Umum'): ?>
                <div class="detail-row">
                    <span class="label">Program</span>
                    <span class="value"><?= htmlspecialchars($donation['program_name']) ?></span>
                </div>
                <?php endif; ?>
                <div class="detail-row">
                    <span class="label">Nominal</span>
                    <span class="value nominal"><?= formatRupiah($donation['amount']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Status</span>
                    <span class="value status <?= $statusInfo['class'] ?>">
                        <i class="<?= $statusInfo['icon'] ?>"></i>
                        <?= $statusInfo['label'] ?>
                    </span>
                </div>
                <?php if (!empty($donation['message'])): ?>
                <div class="detail-row message-row">
                    <span class="label">Pesan/Doa</span>
                    <span class="value message"><?= nl2br(htmlspecialchars($donation['message'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="index.php?page=donasi" class="btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Donasi</span>
            </a>
            <a href="https://wa.me/<?= $admin_wa ?>?text=Halo,%20saya%20ingin%20mengecek%20status%20donasi%20dengan%20ID:%20<?= urlencode($donation['transaction_id']) ?>"
                class="btn-whatsapp" target="_blank">
                <i class="fab fa-whatsapp"></i>
                <span>Hubungi Admin</span>
            </a>
        </div>

        <?php else: ?>
        <!-- No Transaction Found -->
        <div class="konfirmasi-header error">
            <div class="success-icon error">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h1>Transaksi Tidak Ditemukan</h1>
            <p class="subtitle">ID transaksi tidak valid atau tidak ditemukan di sistem kami.</p>
        </div>

        <div class="action-buttons center">
            <a href="index.php?page=donasi" class="btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Donasi</span>
            </a>
            <a href="index.php" class="btn-primary">
                <i class="fas fa-home"></i>
                <span>Ke Beranda</span>
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>
