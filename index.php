<?php require_once 'config/db.php';
session_start();
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-Aspirasi Sekolah</title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>

  <?php include_once __DIR__ . '/includes/navbar.php'; ?>

  <div class="container">
    <div class="hero">
      <div class="hero-tag">Platform Aspirasi Digital</div>
      <h1>Suaramu Penting<br><span>untuk Sekolah Kita</span></h1>
      <p>Sampaikan ide, kritik, dan saran dengan mudah dan transparan. Bersama kita wujudkan lingkungan sekolah yang lebih baik.</p>
    </div>

    <div class="section-label">Fitur Utama</div>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">📝</div>
        <h3>Input Online</h3>
        <p>Kirim aspirasi kapan saja tanpa harus datang langsung ke sekolah.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">📊</div>
        <h3>Monitoring Status</h3>
        <p>Pantau perkembangan aspirasi secara realtime dan transparan.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">💬</div>
        <h3>Feedback Admin</h3>
        <p>Dapatkan tanggapan langsung dari pihak sekolah dengan cepat.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🔒</div>
        <h3>Transparan & Aman</h3>
        <p>Data tersimpan rapi dan terkelola dengan sistem yang terpercaya.</p>
      </div>
    </div>

    <div class="how-section">
      <div class="how-left">
        <div class="section-label">Cara Menggunakan</div>
        <h2>Mudah dalam <span>4 Langkah</span></h2>
        <p>Proses pengiriman aspirasi dirancang sesederhana mungkin agar semua orang bisa melakukannya.</p>
      </div>
      <div class="steps">
        <div class="step">
          <div class="step-num">01</div>
          <div class="step-text">
            <h4>Klik Input Aspirasi</h4>
            <p>Buka menu Input Aspirasi di navigasi atas</p>
          </div>
        </div>
        <div class="step">
          <div class="step-num">02</div>
          <div class="step-text">
            <h4>Pilih NIS & Kategori</h4>
            <p>Pilih NIS kamu dan lengkapi formulir</p>
          </div>
        </div>
        <div class="step">
          <div class="step-num">03</div>
          <div class="step-text">
            <h4>Submit Laporan</h4>
            <p>Kirimkan aspirasi ke sistem</p>
          </div>
        </div>
        <div class="step">
          <div class="step-num">04</div>
          <div class="step-text">
            <h4>Pantau Status</h4>
            <p>Cek perkembangan di menu Aspirasi</p>
          </div>
        </div>
      </div>
    </div>

    <div class="cta-banner">
      <div>
        <h3>Punya Ide atau Masalah?</h3>
        <p>Sampaikan sekarang dan bantu sekolah menjadi lebih baik!</p>
      </div>
      <a href="/views/input.php" class="btn btn-primary">Buat Aspirasi →</a>
    </div>
  </div>

  <?php include_once 'includes/footer.php'; ?>
</body>

</html>