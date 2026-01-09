<?php
/**
 * Donation Page
 * Displays bank accounts and donation form with dynamic program selection
 * Accessed via: index.php?page=donasi
 */

// Establish database connection if not already set (reusing admin/koneksi.php logic if needed)
// Assuming $conn (mysqli) is available from index.php include of admin/koneksi.php
// But we want to use PHP PDO preferably for clean code if possible, or stick to mysqli if that's the project standard.
// detail_program.php used PDO. The project seems mixed.
// I'll check if $conn is available. index.php has `include 'admin/koneksi.php';`.
// I'll use a local PDO connection for this file to ensure "clean code" and independence, or reuse $conn if simple.
// Given detail_program.php used PDO, I will use PDO for the program fetching to be consistent with my previous work.

// Database Connection (PDO)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'yayasan_cms';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fail silently or handle error - fallback to empty programs
    $pdo = null;
    error_log("DB Connection failed: " . $e->getMessage());
}

// Fetch Active Programs for Dropdown
$programs = [];
if ($pdo) {
    try {
        // Assuming 'is_active' or similar exists, or just fetch all. 
        // Checking schema, campaign_programs has no is_active column visible in previous view, mostly standard columns.
        // We'll just fetch all or order by created_at DESC.
        $stmt = $pdo->query("SELECT id, title FROM campaign_programs ORDER BY id DESC");
        $programs = $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching programs: " . $e->getMessage());
    }
}

// Get WhatsApp Number from Site Settings (Global Variable)
$admin_wa = isset($site_settings['whatsapp_number']) ? preg_replace('/[^0-9]/', '', $site_settings['whatsapp_number']) : '6281234567890';
if (empty($admin_wa)) $admin_wa = '6281234567890'; // Fallback

// Bank Accounts Configuration (Clean Array)
$bank_accounts = [
    [
        'name' => 'Bank Central Asia (BCA)',
        'holder' => 'Yayasan Y-ibbi',
        'number' => '1234567890',
        'logo' => 'https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg',
        'class' => ''
    ],
    [
        'name' => 'Bank Mandiri',
        'holder' => 'Yayasan Y-ibbi',
        'number' => '9876543210',
        'logo' => 'https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg',
        'class' => 'mandiri'
    ],
    [
        'name' => 'Bank Syariah Indonesia (BSI)',
        'holder' => 'Yayasan Y-ibbi',
        'number' => '7171234567',
        'logo' => 'https://upload.wikimedia.org/wikipedia/commons/a/a0/Bank_Syariah_Indonesia.svg',
        'class' => 'bsi'
    ]
];

// E-Wallets
$ewallets = [
    ['name' => 'GoPay', 'number' => '081234567890'],
    ['name' => 'OVO', 'number' => '081234567890'],
    ['name' => 'DANA', 'number' => '081234567890']
];
?>

<!-- Page Specific CSS -->
<link rel="stylesheet" href="template/donasi.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<section class="donasi-section">
    <div class="container">
        <!-- Section Header -->
        <div class="section-header">
            <div class="section-badge">
                <i class="fas fa-hand-holding-heart"></i>
                <span>Berbagi Kebaikan</span>
            </div>
            <h1 class="section-title">Donasi Sekarang</h1>
            <div class="title-divider"></div>
            <p class="section-desc">
                Mari bersama-sama berbagi kebaikan. Setiap donasi Anda akan membantu
                program-program sosial keagamaan kami untuk masyarakat yang membutuhkan.
            </p>
        </div>

        <!-- Donasi Content -->
        <div class="donasi-wrapper">
            <!-- Left Column: Bank Info -->
            <div class="bank-column">
                <h2 class="column-title">
                    <i class="fas fa-building-columns"></i>
                    Transfer Bank
                </h2>
                <p class="column-desc">Silakan transfer donasi Anda ke salah satu rekening berikut:</p>

                <?php foreach ($bank_accounts as $index => $bank): ?>
                <div class="bank-card">
                    <div class="bank-logo <?= $bank['class'] ?>">
                        <img src="<?= $bank['logo'] ?>" alt="<?= $bank['name'] ?>">
                    </div>
                    <div class="bank-info">
                        <span class="bank-name"><?= $bank['name'] ?></span>
                        <span class="account-name"><?= $bank['holder'] ?></span>
                        <div class="account-number">
                            <span id="bank-num-<?= $index ?>"><?= $bank['number'] ?></span>
                            <button class="copy-btn" onclick="copyToClipboard('bank-num-<?= $index ?>')" title="Salin">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- E-Wallet Section -->
                <div class="ewallet-section">
                    <h3 class="ewallet-title">E-Wallet</h3>
                    <div class="ewallet-list">
                        <?php foreach ($ewallets as $wallet): ?>
                        <div class="ewallet-item">
                            <i class="fas fa-wallet"></i>
                            <span><?= $wallet['name'] ?>: <?= $wallet['number'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Donation Form -->
            <div class="form-column">
                <h2 class="column-title">
                    <i class="fas fa-file-pen"></i>
                    Konfirmasi Donasi
                </h2>
                <p class="column-desc">Isi form berikut untuk konfirmasi donasi Anda:</p>

                <form class="donasi-form" id="donasiForm">
                    <!-- Dropdown Program (Added Feature) -->
                    <div class="form-group">
                        <label for="program_id">
                            <i class="fas fa-hand-holding-heart"></i>
                            Pilih Program Donasi
                        </label>
                        <select id="program_id" name="program_id" class="form-control" style="padding: 14px 16px; border: 1.5px solid var(--neutral-200); border-radius: var(--radius-md); width: 100%;">
                            <option value="">-- Donasi Umum --</option>
                            <?php 
                            $selected_id = $_GET['program_id'] ?? '';
                            foreach ($programs as $prog): 
                                $is_selected = ($prog['id'] == $selected_id) ? 'selected' : '';
                            ?>
                            <option value="<?= htmlspecialchars($prog['title']) ?>" <?= $is_selected ?>>
                                <?= htmlspecialchars($prog['title']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="nama">
                            <i class="fas fa-user"></i>
                            Nama Lengkap
                        </label>
                        <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap Anda" required>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            Email
                        </label>
                        <input type="email" id="email" name="email" placeholder="contoh@email.com" required>
                    </div>

                    <div class="form-group">
                        <label for="nohp">
                            <i class="fas fa-phone"></i>
                            No. HP / WhatsApp
                        </label>
                        <input type="tel" id="nohp" name="nohp" placeholder="08xxxxxxxxxx" required>
                    </div>

                    <div class="form-group">
                        <label for="nominal">
                            <i class="fas fa-money-bill-wave"></i>
                            Nominal Donasi
                        </label>
                        <div class="nominal-input">
                            <span class="currency">Rp</span>
                            <input type="text" id="nominal" name="nominal" placeholder="100.000" required>
                        </div>
                    </div>

                    <!-- Quick Amount Buttons -->
                    <div class="quick-amounts">
                        <button type="button" class="quick-btn" onclick="setAmount('50000')">50rb</button>
                        <button type="button" class="quick-btn" onclick="setAmount('100000')">100rb</button>
                        <button type="button" class="quick-btn" onclick="setAmount('250000')">250rb</button>
                        <button type="button" class="quick-btn" onclick="setAmount('500000')">500rb</button>
                        <button type="button" class="quick-btn" onclick="setAmount('1000000')">1Jt</button>
                    </div>

                    <div class="form-group">
                        <label for="pesan">
                            <i class="fas fa-message"></i>
                            Pesan / Doa (Opsional)
                        </label>
                        <textarea id="pesan" name="pesan" rows="3" placeholder="Tuliskan pesan atau doa Anda..."></textarea>
                    </div>

                    <!-- Upload Bukti Pembayaran -->
                    <div class="form-group">
                        <label for="bukti">
                            <i class="fas fa-image"></i>
                            Upload Bukti Pembayaran
                        </label>
                        <div class="upload-area" id="uploadArea">
                            <input type="file" id="bukti" name="bukti" accept="image/*" hidden>
                            <div class="upload-content" id="uploadContent">
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-arrow-up"></i>
                                </div>
                                <p class="upload-text">Klik atau drag file ke sini</p>
                                <p class="upload-hint">Format: JPG, PNG, JPEG (Maks. 5MB)</p>
                            </div>
                            <div class="upload-preview" id="uploadPreview" style="display: none;">
                                <img id="previewImage" src="" alt="Preview">
                                <button type="button" class="remove-file" id="removeFile">
                                    <i class="fas fa-times"></i>
                                </button>
                                <p class="file-name" id="fileName"></p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i>
                        <span>Konfirmasi Donasi</span>
                    </button>
                </form>

                <!-- WhatsApp Consultation -->
                <div class="whatsapp-section">
                    <div class="divider">
                        <span>atau</span>
                    </div>
                    <a href="https://wa.me/<?= $admin_wa ?>?text=Halo,%20saya%20ingin%20berkonsultasi%20tentang%20donasi"
                        class="btn-whatsapp" target="_blank">
                        <i class="fab fa-whatsapp"></i>
                        <span>Konsultasi via WhatsApp</span>
                    </a>
                    <p class="whatsapp-note">
                        Butuh bantuan? Silakan hubungi kami via WhatsApp untuk bertanya seputar donasi.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Global Constants
    const ADMIN_WA = "<?= $admin_wa ?>";

    // Copy to clipboard function
    function copyToClipboard(elementId) {
        const text = document.getElementById(elementId).textContent;
        navigator.clipboard.writeText(text).then(() => {
            showToast('Nomor rekening berhasil disalin!');
        });
    }

    // Set amount function
    function setAmount(amount) {
        const formatted = new Intl.NumberFormat('id-ID').format(amount);
        document.getElementById('nominal').value = formatted;
        document.querySelectorAll('.quick-btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
    }

    // Format nominal input
    document.getElementById('nominal').addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value) {
            value = new Intl.NumberFormat('id-ID').format(value);
        }
        e.target.value = value;
    });

    // Form submission
    document.getElementById('donasiForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const nama = document.getElementById('nama').value;
        const email = document.getElementById('email').value;
        const nohp = document.getElementById('nohp').value;
        const nominal = document.getElementById('nominal').value;
        const pesan = document.getElementById('pesan').value;
        const program = document.getElementById('program_id').value;

        // Create WhatsApp message
        let message = `*Konfirmasi Donasi*%0A%0A`;
        if (program) message += `*Program:* ${program}%0A`;
        message += `*Nama:* ${nama}%0A`;
        message += `*Email:* ${email}%0A`;
        message += `*No. HP:* ${nohp}%0A`;
        message += `*Nominal:* Rp ${nominal}%0A`;
        if (pesan) {
            message += `*Pesan:* ${pesan}%0A`;
        }
        message += `*Mohon dicek bukti transfer saya.*`;

        // Open WhatsApp
        window.open(`https://wa.me/${ADMIN_WA}?text=${message}`, '_blank');
    });

    // Toast notification
    function showToast(message) {
        let toast = document.querySelector('.toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.className = 'toast';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
        }, 2000);
    }

    // File Upload Handling
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('bukti');
    const uploadContent = document.getElementById('uploadContent');
    const uploadPreview = document.getElementById('uploadPreview');
    const previewImage = document.getElementById('previewImage');
    const fileName = document.getElementById('fileName');
    const removeFile = document.getElementById('removeFile');

    uploadArea.addEventListener('click', () => fileInput.click());
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('dragover'));
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        if (e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]);
    });
    fileInput.addEventListener('change', (e) => {
        if (e.target.files[0]) handleFile(e.target.files[0]);
    });

    function handleFile(file) {
        if (!file.type.match('image.*')) {
            showToast('Harap pilih file gambar (JPG, PNG, JPEG)');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            showToast('Ukuran file maksimal 5MB');
            return;
        }
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImage.src = e.target.result;
            fileName.textContent = file.name;
            uploadContent.style.display = 'none';
            uploadPreview.style.display = 'flex';
        };
        reader.readAsDataURL(file);
    }

    removeFile.addEventListener('click', (e) => {
        e.stopPropagation();
        fileInput.value = '';
        previewImage.src = '';
        fileName.textContent = '';
        uploadContent.style.display = 'flex';
        uploadPreview.style.display = 'none';
    });
</script>
