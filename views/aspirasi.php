<?php
require_once '../config/db.php';
session_start();
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';


if (isset($_POST['submit_modal'])) {

  $id       = (int) $_POST['id_pelaporan'];
  $status   = mysqli_real_escape_string($conn, $_POST['status']);
  $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);

  // cek apakah data sudah ada di tabel aspirasi
  $cek = mysqli_query($conn, "SELECT id_pelaporan FROM aspirasi WHERE id_pelaporan = $id");

  if (mysqli_num_rows($cek) > 0) {
    // UPDATE
    mysqli_query($conn, "
        UPDATE aspirasi 
        SET status = '$status', feedback = '$feedback' 
        WHERE id_pelaporan = $id
      ");
  } else {
    // INSERT
    mysqli_query($conn, "
        INSERT INTO aspirasi (id_pelaporan, status, feedback) 
        VALUES ($id, '$status', '$feedback')
      ");
  }

  header('Location: aspirasi.php');
  exit;
}

$query = "SELECT 
            input_aspirasi.id_pelaporan,
            input_aspirasi.nis,
            siswa.kelas,
            kategori.ket_kategori,
            input_aspirasi.lokasi,
            input_aspirasi.ket,
            input_aspirasi.created_at AS tanggal,
            aspirasi.status,
            aspirasi.feedback
          FROM input_aspirasi
          JOIN siswa ON input_aspirasi.nis = siswa.nis
          JOIN kategori ON input_aspirasi.id_kategori = kategori.id_kategori
          LEFT JOIN aspirasi ON input_aspirasi.id_pelaporan = aspirasi.id_pelaporan
          ORDER BY input_aspirasi.id_pelaporan DESC";

$result = mysqli_query($conn, $query);

$aspirasi = [];

while ($row = mysqli_fetch_assoc($result)) {
  // default status
  if (!$row['status']) {
    $row['status'] = 'menunggu';
  }

  // format tanggal jadi DD-MM-YYYY
  $row['tanggal'] = date('d-m-Y', strtotime($row['tanggal']));

  $aspirasi[] = $row;
}

// ── Filter dari GET ──
$filter_bulan    = $_GET['bulan']  ?? '';
$filter_tahun    = $_GET['tahun']  ?? '';
$filter_tgl_dari = $_GET['dari']   ?? '';
$filter_tgl_smp  = $_GET['sampai'] ?? '';
$search_nis      = $_GET['nis']    ?? '';

// Tambahkan _ts ke setiap baris (DD-MM-YYYY → timestamp)
foreach ($aspirasi as &$row) {
  [$d, $m, $y]  = explode('-', $row['tanggal']);
  $row['_ts']   = mktime(0, 0, 0, (int)$m, (int)$d, (int)$y);
}
unset($row);

// ── Filter ──
$filtered = array_values(array_filter($aspirasi, function ($row) use (
  $filter_bulan,
  $filter_tahun,
  $filter_tgl_dari,
  $filter_tgl_smp,
  $search_nis,
  $isAdmin,
) {
  $ts = $row['_ts'];
  if ($filter_bulan && date('m', $ts) !== str_pad($filter_bulan, 2, '0', STR_PAD_LEFT)) return false;
  if ($filter_tahun && date('Y', $ts) !== $filter_tahun) return false;
  if ($filter_tgl_dari && $ts < strtotime($filter_tgl_dari . ' 00:00:00')) return false;
  if ($filter_tgl_smp  && $ts > strtotime($filter_tgl_smp  . ' 23:59:59')) return false;
  if ($search_nis) {

    // 🔒 SISWA → hanya boleh exact NIS
    if (!$isAdmin) {
      if ($row['nis'] != $search_nis) return false;
    }

    // 👨‍💼 ADMIN → bebas search
    else {
      $q = strtolower($search_nis);
      if (
        strpos(strtolower((string)$row['nis']), $q) === false
        && stripos($row['kelas'], $q) === false
        && stripos($row['ket_kategori'], $q) === false
      ) return false;
    }
  }
  return true;
}));

// ── Statistik ──
$total    = count($filtered);
$menunggu = count(array_filter($filtered, fn($r) => $r['status'] === 'menunggu'));
$proses   = count(array_filter($filtered, fn($r) => $r['status'] === 'proses'));
$selesai  = count(array_filter($filtered, fn($r) => $r['status'] === 'selesai'));
$pct      = fn($n) => $total ? round($n / $total * 100) : 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Aspirasi — E-Aspirasi</title>
  <link rel="stylesheet" href="../css/style.css">
</head>

<body>

  <?php include_once '../includes/navbar.php'; ?>

  <div class="container">
    <div class="page-title">Data Aspirasi Siswa</div>

    <!-- ── STATISTIK (hanya tampil jika admin) ── -->
    <?php if ($isAdmin): ?>
      <div class="stats-section">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-num"><?= $total ?></div>
            <div class="stat-lbl">Total Masuk</div>
          </div>
          <div class="stat-card">
            <div class="stat-num stat-menunggu"><?= $menunggu ?></div>
            <div class="stat-lbl">Menunggu</div>
            <div class="stat-bar">
              <div class="stat-fill stat-fill-menunggu" style="width:<?= $pct($menunggu) ?>%"></div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-num stat-proses"><?= $proses ?></div>
            <div class="stat-lbl">Diproses</div>
            <div class="stat-bar">
              <div class="stat-fill stat-fill-proses" style="width:<?= $pct($proses) ?>%"></div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-num stat-selesai"><?= $selesai ?></div>
            <div class="stat-lbl">Selesai</div>
            <div class="stat-bar">
              <div class="stat-fill stat-fill-selesai" style="width:<?= $pct($selesai) ?>%"></div>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- ── FILTER ADMIN ── -->
    <?php if ($isAdmin): ?>
      <form method="GET" class="filter-bar">
        <div class="filter-group">
          <label>Cari NIS / Kelas / Kategori</label>
          <input type="text" name="nis" class="form-control"
            placeholder="NIS / Kelas / Kategori" value="<?= htmlspecialchars($search_nis) ?>">
        </div>
        <div class="filter-group">
          <label>Bulan</label>
          <select name="bulan" class="form-control">
            <option value="">Semua Bulan</option>
            <?php
            $bln = [
              '01' => 'Januari',
              '02' => 'Februari',
              '03' => 'Maret',
              '04' => 'April',
              '05' => 'Mei',
              '06' => 'Juni',
              '07' => 'Juli',
              '08' => 'Agustus',
              '09' => 'September',
              '10' => 'Oktober',
              '11' => 'November',
              '12' => 'Desember'
            ];
            foreach ($bln as $num => $nm):
              $sel = (str_pad($filter_bulan, 2, '0', STR_PAD_LEFT) === $num) ? 'selected' : '';
            ?>
              <option value="<?= $num ?>" <?= $sel ?>><?= $nm ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="filter-group">
          <label>Tahun</label>
          <select name="tahun" class="form-control">
            <option value="">Semua Tahun</option>
            <?php foreach (['2026', '2025', '2024'] as $y): ?>
              <option value="<?= $y ?>" <?= $filter_tahun === $y ? 'selected' : '' ?>><?= $y ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="filter-group">
          <label>Dari Tanggal</label>
          <input type="date" name="dari" class="form-control" value="<?= htmlspecialchars($filter_tgl_dari) ?>">
        </div>
        <div class="filter-group">
          <label>Sampai Tanggal</label>
          <input type="date" name="sampai" class="form-control" value="<?= htmlspecialchars($filter_tgl_smp) ?>">
        </div>
        <div class="filter-actions">
          <button type="submit" class="btn btn-primary">Terapkan</button>
          <a href="aspirasi.php" class="btn btn-secondary">Reset</a>
        </div>
      </form>
    <?php endif; ?>

    <!-- ── FILTER SISWA ── -->
    <?php if (!$isAdmin): ?>
      <form method="GET" class="nisn-search">
        <div class="filter-group">
          <label>Cari berdasarkan NIS kamu</label>
          <input type="number" name="nis" class="form-control"
            placeholder="Masukkan NIS kamu"
            value="<?= htmlspecialchars($search_nis) ?>">
        </div>

        <button type="submit" class="btn btn-primary">Cari</button>
        <a href="aspirasi.php" class="btn btn-secondary">Reset</a>
      </form>
    <?php endif; ?>

    <!-- ── META ── -->
    <div class="table-meta" id="table-meta" style="display:none;">
      <span class="table-meta-count"><?= count($filtered) ?> aspirasi ditemukan</span>
    </div>

    <!-- ── TABLE ── -->
    <div class="card" style="padding:0;overflow:hidden;">
      <div class="table-wrap">
        <table class="asp-table">
          <thead>
            <tr>
              <?php if ($isAdmin): ?>
                <th>NIS</th>
                <th>Kelas</th>
                <th>Kategori</th>
                <th>Lokasi</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th>Feedback</th>
                <th>Tanggal</th>
                <th>Aksi</th>
              <?php else: ?>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Lokasi</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th>Feedback</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody id="tbody">
            <?php foreach ($filtered as $row): ?>
              <tr>
                <?php if ($isAdmin): ?>
                  <td><?= htmlspecialchars($row['nis']) ?></td>
                  <td><?= htmlspecialchars($row['kelas']) ?></td>
                  <td><span class="badge-kat"><?= htmlspecialchars($row['ket_kategori']) ?></span></td>
                  <td><?= htmlspecialchars($row['lokasi']) ?></td>
                  <td><?= htmlspecialchars(mb_strimwidth($row['ket'], 0, 50, '…')) ?></td>
                  <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                  <td><?= $row['feedback'] ? htmlspecialchars(mb_strimwidth($row['feedback'], 0, 60, '…')) : '—' ?></td>
                  <td><?= $row['tanggal'] ?></td>
                  <td>
                    <button class="btn-update" onclick='openModal(<?= htmlspecialchars(json_encode($row)) ?>)'>Kelola</button>
                  </td>

                <?php else: ?>
                  <td><?= $row['tanggal'] ?></td>
                  <td><span class="badge-kat"><?= htmlspecialchars($row['ket_kategori']) ?></span></td>
                  <td><?= htmlspecialchars($row['lokasi']) ?></td>
                  <td><?= htmlspecialchars(mb_strimwidth($row['ket'], 0, 50, '…')) ?></td>
                  <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                  <td><?= $row['feedback'] ? htmlspecialchars($row['feedback']) : '—' ?></td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($search_nis && empty($filtered)): ?>
        <div class="empty-state">
          <p>Tidak ada data aspirasi yang sesuai dengan filter.</p>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <!-- ── MODAL KELOLA (Admin) ── -->
  <div class="modal-overlay" id="modalBg" onclick="closeModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
        <span style="font-size:0.72rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-muted);">kelola aspirasi</span>
        <button onclick="closeModal()"
          style="background:none;border:none;cursor:pointer;font-size:1.1rem;color:var(--text-muted);line-height:1;">✕</button>
      </div>

      <!-- Info aspirasi -->
      <div class="modal-info" id="m-info"></div>

      <form method="POST" action="aspirasi.php">
        <input type="hidden" name="id_pelaporan" id="m-id">

        <div class="form-group">
          <label>Ubah Status</label>
          <select name="status" id="m-status" class="form-control">
            <option value="menunggu">Menunggu</option>
            <option value="proses">Diproses</option>
            <option value="selesai">Selesai</option>
          </select>
        </div>

        <div class="form-group">
          <label>Feedback <span style="color:var(--text-muted);font-weight:400;">(opsional)</span></label>
          <textarea name="feedback" id="m-feedback" class="form-control"
            rows="4" placeholder="Tulis respons atau tindak lanjut..."></textarea>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
          <button type="submit" class="btn btn-primary" name="submit_modal">Simpan →</button>
        </div>
      </form>
    </div>
  </div>

  <?php include_once '../includes/footer.php'; ?>

  <script>
    // ── Modal ──
    function openModal(d) {
      document.getElementById('m-id').value = d.id_pelaporan;
      document.getElementById('m-status').value = d.status || 'menunggu';
      document.getElementById('m-feedback').value = d.feedback || '';

      const ket = d.ket.length > 80 ? d.ket.slice(0, 80) + '…' : d.ket;
      document.getElementById('m-info').innerHTML = `
      <strong>${d.kelas}</strong> &nbsp;·&nbsp; NIS: ${d.nis}<br>
      <span style="color:var(--text-muted);font-size:0.82rem;">${d.lokasi}</span><br>
      <span style="color:var(--text-muted);font-size:0.82rem;margin-top:0.35rem;display:block;">${ket}</span>`;

      document.getElementById('modalBg').classList.add('show');
      document.body.style.overflow = 'hidden';
    }

    function closeModal() {
      document.getElementById('modalBg').classList.remove('show');
      document.body.style.overflow = '';
    }

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') closeModal();
    });
  </script>
</body>

</html>