<?php
require_once '../config/db.php';
session_start();
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';


$is_visible_err = false;
$is_visible_ok = false;
$err = "";

if (isset($_SESSION['success_msg'])) {
  $is_visible_ok = true;
  unset($_SESSION['success_msg']);
}

if (isset($_POST['submit'])) {
  $nis = $_POST['nis'];
  $kategori = $_POST['kategori'];
  $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
  $keterangan = mysqli_real_escape_string($conn, $_POST['ket']);

  $insert = mysqli_query($conn, "INSERT INTO input_aspirasi (nis, id_kategori, lokasi, ket) VALUES ('$nis', '$kategori', '$lokasi', '$keterangan')");

  if ($insert) {
    $_SESSION['success_msg'] = true; // Simpan tanda sukses di session
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
  } else {
    $is_visible_err = true;
    $err = "Gagal menyimpan:" . mysqli_error($conn);
  }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Input Aspirasi — E-Aspirasi</title>
  <link rel="stylesheet" href="../css/style.css">
</head>

<body>

  <?php include_once '../includes/navbar.php'; ?>

  <div class="container">
    <div class="form-wrap">
      <div class="page-title">Input Aspirasi</div>

      <!-- Alert -->
      <div class="alert alert-success <?= $is_visible_ok ? 'show' : ''; ?> " id="alert-ok">
        Aspirasi berhasil dikirim! Terima kasih.
      </div>
      <div class="alert alert-danger <?= $is_visible_err ? 'show' : ''; ?> " id="alert-err">
        <?= $err; ?>
      </div>

      <div class="card">
        <!-- PANDUAN -->
        <div class="input-guide">
          <div class="input-guide-step">
            <div class="guide-num">01 —</div>
            <div class="guide-text">
              <h4>Identitas & Kategori</h4>
              <p>Pilih NIS Anda dan kategori yang paling sesuai dengan aspirasi yang ingin disampaikan.</p>
            </div>
          </div>
          <div class="input-guide-step">
            <div class="guide-num">02 —</div>
            <div class="guide-text">
              <h4>Lokasi</h4>
              <p>Cantumkan lokasi kejadian atau tempat yang relevan dengan aspirasi Anda.</p>
            </div>
          </div>
          <div class="input-guide-step">
            <div class="guide-num">03 —</div>
            <div class="guide-text">
              <h4>Keterangan</h4>
              <p>Jelaskan aspirasi Anda secara detail dan jelas agar mudah ditindaklanjuti.</p>
            </div>
          </div>
        </div>

        <form action="" method="post">
          <!-- PILIH SISWA -->
          <div class="form-group">
            <label>Pilih Siswa (NIS — Kelas)</label>
            <select class="form-control" name="nis" id="sel-siswa" onchange="onSiswaChange()" required>
              <option value="">-- Pilih NIS / Kelas kamu --</option>
              <?php
              $ambil_nis = mysqli_query($conn, "SELECT nis, kelas FROM siswa ORDER BY kelas ASC");

              while ($a_nis = mysqli_fetch_assoc($ambil_nis)) { ?>
                <option value="<?= $a_nis['nis'] ?>" data-kelas="<?= htmlspecialchars($a_nis['kelas']) ?>">
                  <?= $a_nis['nis'] ?> — <?= htmlspecialchars($a_nis['kelas']) ?>
                </option>
              <?php } ?>

            </select>
          </div>

          <div class="siswa-info" id="siswa-info">
            <strong>NIS: <span id="info-nis">-</span></strong>
            <span>Kelas: <b id="info-kelas">-</b></span>
          </div>

          <div class="grid-2">
            <!-- PILIH KATEGORI -->
            <div class="form-group">
              <label>Kategori</label>
              <select class="form-control" name="kategori" id="sel-kategori" required>
                <option value="">-- Pilih kategori --</option>
                <?php $kat = mysqli_query($conn, "SELECT * FROM kategori ORDER BY ket_kategori ASC");
                while ($k = mysqli_fetch_assoc($kat)) { ?>
                  <option value="<?= $k['id_kategori'] ?>">
                    <?= htmlspecialchars($k['ket_kategori']) ?>
                  </option>
                <?php } ?>
              </select>
            </div>
            <div class="form-group">
              <label>Lokasi / Ruangan</label>
              <input type="text" class="form-control" name="lokasi" id="inp-lokasi" placeholder="Contoh: Ruang Lab 1" required>
            </div>
          </div>

          <div class="form-group">
            <label>Keterangan / Deskripsi Masalah</label>
            <textarea class="form-control" name="ket" id="inp-ket"
              placeholder="Jelaskan permasalahan atau aspirasi kamu secara detail..." required></textarea>
          </div>

          <div class="input-notice">
            <span class="input-notice-icon">■</span>
            <span>Aspirasi Anda akan tampil secara publik. Gunakan bahasa yang sopan untuk membangun sekolah yang lebih baik.</span>
          </div>

          <div style="display:flex;gap:0.75rem;justify-content:flex-end;margin-top:1.25rem;">
            <button class="btn btn-secondary" onclick="resetForm()">Reset</button>
            <button class="btn btn-primary" name="submit" type="submit">Kirim Aspirasi</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php include_once '../includes/footer.php'; ?>

  <script>
    <?php if ($is_visible_ok) { ?>
      window.onload = function() {
        document.getElementById('alert-ok').scrollIntoView({
          behavior: 'smooth',
          block: "center"
        });
      }
    <?php } ?>

    function onSiswaChange() {
      const sel = document.getElementById('sel-siswa');
      const opt = sel.options[sel.selectedIndex];
      const info = document.getElementById('siswa-info');
      if (sel.value) {
        document.getElementById('info-nis').textContent = sel.value;
        document.getElementById('info-kelas').textContent = opt.dataset.kelas;
        info.style.display = 'block';
      } else {
        info.style.display = 'none';
      }
    }

    function resetForm() {
      document.getElementById('sel-siswa').value = '';
      document.getElementById('sel-kategori').value = '';
      document.getElementById('inp-lokasi').value = '';
      document.getElementById('inp-ket').value = '';
      document.getElementById('siswa-info').style.display = 'none';
      document.getElementById('alert-ok').classList.remove('show');
      document.getElementById('alert-err').classList.remove('show');
    }
  </script>
</body>

</html>