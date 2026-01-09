<?php
/**
 * Contact Page
 * Displays contact form and information from database
 * Accessed via: index.php?page=contact
 */

// Helper to get safe value from site_settings (loaded in index.php)
function getSetting($key, $default = '') {
    global $site_settings;
    return !empty($site_settings[$key]) ? htmlspecialchars($site_settings[$key]) : $default;
}

// Prepare data
$address = getSetting('address');
$email = getSetting('email_primary');
$phone = getSetting('phone_primary');
$whatsapp = getSetting('whatsapp_number');
$working_hours = getSetting('working_hours');
$whatsapp_clean = preg_replace('/[^0-9]/', '', $whatsapp); // For links

// Social Media Links
$social_links = [
    'instagram' => ['url' => getSetting('instagram_url'), 'icon' => 'fab fa-instagram'],
    'facebook'  => ['url' => getSetting('facebook_url'), 'icon' => 'fab fa-facebook'],
    'youtube'   => ['url' => getSetting('youtube_url'), 'icon' => 'fab fa-youtube'],
    'twitter'   => ['url' => getSetting('twitter_url'), 'icon' => 'fab fa-twitter'], // Using twitter icon for X if needed
];
?>

<!-- Load Page Specific CSS -->
<link rel="stylesheet" href="template/kontak.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<section class="kontak-section">
    <div class="container">
        <!-- Section Header -->
        <div class="section-header">
            <div class="section-badge">
                <i class="fas fa-envelope"></i>
                <span>Hubungi Kami</span>
            </div>
            <h1>Kontak</h1>
            <p>Ada pertanyaan atau ingin berkolaborasi? Silakan hubungi kami melalui form di bawah ini.</p>
        </div>

        <!-- Contact Content -->
        <div class="kontak-wrapper">
            <!-- Contact Form -->
            <div class="form-card">
                <form id="kontakForm" class="kontak-form">
                    <!-- Nama -->
                    <div class="form-group">
                        <label for="nama">
                            <i class="fas fa-user"></i>
                            Nama Lengkap
                        </label>
                        <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap Anda" required>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            Email
                        </label>
                        <input type="email" id="email" name="email" placeholder="contoh@email.com" required>
                    </div>

                    <!-- No WhatsApp dengan Country Code -->
                    <div class="form-group">
                        <label for="nowa">
                            <i class="fab fa-whatsapp"></i>
                            Nomor WhatsApp
                        </label>
                        <div class="phone-input">
                            <div class="country-select">
                                <select id="countryCode" name="countryCode">
                                    <option value="+62" data-country="🇮🇩" selected>🇮🇩 +62</option>
                                    <option value="+1" data-country="🇺🇸">🇺🇸 +1</option>
                                    <option value="+44" data-country="🇬🇧">🇬🇧 +44</option>
                                    <option value="+60" data-country="🇲🇾">🇲🇾 +60</option>
                                    <option value="+65" data-country="🇸🇬">🇸🇬 +65</option>
                                </select>
                            </div>
                            <input type="tel" id="nowa" name="nowa" placeholder="8123456789" required>
                        </div>
                        <span class="input-hint">Masukkan nomor tanpa angka 0 di depan</span>
                    </div>

                    <!-- Pesan -->
                    <div class="form-group">
                        <label for="pesan">
                            <i class="fas fa-message"></i>
                            Pesan
                        </label>
                        <textarea id="pesan" name="pesan" rows="4" placeholder="Tuliskan pesan Anda..." required></textarea>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="form-buttons">
                        <button type="submit" class="btn-whatsapp">
                            <i class="fab fa-whatsapp"></i>
                            <span>Hubungi via WhatsApp</span>
                        </button>
                        <button type="button" class="btn-email" onclick="sendEmail()">
                            <i class="fas fa-envelope"></i>
                            <span>Kirim Email</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Contact Info -->
            <div class="info-card">
                <h3>Informasi Kontak</h3>

                <?php if ($address): ?>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-location-dot"></i>
                    </div>
                    <div class="info-content">
                        <span class="info-label">Alamat</span>
                        <p><?= $address ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($whatsapp || $phone): ?>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <div class="info-content">
                        <span class="info-label">WhatsApp / Telepon</span>
                        <p><?= $whatsapp ?: $phone ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($email): ?>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <span class="info-label">Email</span>
                        <p><?= $email ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($working_hours): ?>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-content">
                        <span class="info-label">Jam Operasional</span>
                        <p><?= $working_hours ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Social Media -->
                <?php 
                $has_social = false;
                foreach($social_links as $link) {
                    if (!empty($link['url']) && $link['url'] != '#') {
                        $has_social = true;
                        break;
                    }
                }
                
                if ($has_social): 
                ?>
                <div class="social-section">
                    <span class="social-label">Ikuti Kami</span>
                    <div class="social-links">
                        <?php foreach ($social_links as $social): ?>
                            <?php if (!empty($social['url']) && $social['url'] != '#'): ?>
                            <a href="<?= $social['url'] ?>" class="social-link" target="_blank">
                                <i class="<?= $social['icon'] ?>"></i>
                            </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
    // Constants from PHP
    const ADMIN_WA = "<?= $whatsapp_clean ?>";
    const ADMIN_EMAIL = "<?= $email ?>";

    // Form submission to WhatsApp
    document.getElementById('kontakForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const nama = document.getElementById('nama').value;
        const email = document.getElementById('email').value;
        const countryCode = document.getElementById('countryCode').value;
        const nowa = document.getElementById('nowa').value;
        const pesan = document.getElementById('pesan').value;

        // Format message
        let message = `*Pesan dari Website*%0A%0A`;
        message += `*Nama:* ${nama}%0A`;
        message += `*Email:* ${email}%0A`;
        message += `*No. WA:* ${countryCode}${nowa}%0A`;
        message += `*Pesan:*%0A${pesan}`;

        // Open WhatsApp with admin number from DB
        window.open(`https://wa.me/${ADMIN_WA}?text=${message}`, '_blank');
    });

    // Send Email function
    function sendEmail() {
        const nama = document.getElementById('nama').value;
        const email = document.getElementById('email').value;
        const pesan = document.getElementById('pesan').value;

        if (!nama || !email || !pesan) {
            alert('Mohon lengkapi semua field terlebih dahulu');
            return;
        }

        const subject = `Pesan dari ${nama} - Website Y-ibbi`;
        const body = `Nama: ${nama}%0AEmail: ${email}%0A%0APesan:%0A${pesan}`;

        window.location.href = `mailto:${ADMIN_EMAIL}?subject=${subject}&body=${body}`;
    }

    // Phone number validation - only numbers
    document.getElementById('nowa').addEventListener('input', function (e) {
        this.value = this.value.replace(/\D/g, '');
    });
</script>
