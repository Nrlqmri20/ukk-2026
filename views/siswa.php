<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
  echo "<script>
    alert('Anda harus login terlebih dahulu');
    window.location.href = '../login.php';
  </script>";
  exit;
}
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';


// ── TAMBAH ──
if (isset($_POST['tambah'])) {
  $nis   = (int) $_POST['nis'];
  $kelas = trim($_POST['kelas']);

  if ($nis && $kelas != '') {

    $kelas = mysqli_real_escape_string($conn, $kelas);

    // cek duplikat
    $cek = mysqli_query($conn, "SELECT * FROM siswa WHERE nis=$nis");

    if (mysqli_num_rows($cek) == 0) {
      mysqli_query($conn, "INSERT INTO siswa (nis, kelas) VALUES ($nis, '$kelas')");

      echo "<script>
        alert('Siswa berhasil ditambahkan');
        window.location.href='siswa.php';
      </script>";
      exit;
    } else {
      echo "<script>alert('NIS sudah terdaftar');</script>";
    }
  }
}

// ── EDIT ──
if (isset($_POST['edit'])) {
  $nis   = (int) $_POST['nis'];
  $kelas = trim($_POST['kelas']);

  if ($kelas != '') {
    $kelas = mysqli_real_escape_string($conn, $kelas);

    mysqli_query($conn, "UPDATE siswa SET kelas='$kelas' WHERE nis=$nis");

    echo "<script>
      alert('Data siswa berhasil diupdate');
      window.location.href='siswa.php';
    </script>";
    exit;
  }
}

// ── HAPUS ──
if (isset($_POST['hapus'])) {
  $nis = (int) $_POST['nis'];

  mysqli_query($conn, "DELETE FROM siswa WHERE nis=$nis");

  echo "<script>
    alert('Siswa berhasil dihapus');
    window.location.href='siswa.php';
  </script>";
  exit;
}

// ── AMBIL DATA ──
$result = mysqli_query($conn, "SELECT * FROM siswa ORDER BY nis DESC");

$siswa = [];
while ($row = mysqli_fetch_assoc($result)) {
  $siswa[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Siswa — E-Aspirasi</title>
  <link rel="stylesheet" href="../css/style.css">
</head>

<body>

  <?php include_once '../includes/navbar.php'; ?>

  <div class="container">
    <div class="page-title">Kelola Data Siswa</div>

    <div class="alert alert-success" id="alert-ok"></div>
    <div class="alert alert-danger" id="alert-err"></div>

    <form method="POST" class="card" style="margin-bottom:1.25rem;">
      <div class="grid-2">
        <div class="form-group">
          <label>NIS</label>
          <input type="number" name="nis" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Kelas</label>
          <input type="text" name="kelas" class="form-control" required>
        </div>

        <button type="submit" name="tambah" class="btn btn-primary">
          Tambah Siswa
        </button>
      </div>
    </form>

    <!-- TABLE -->
    <div class="card" style="padding:0;overflow:hidden;">
      <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
        <span style="font-weight:700;font-size:0.95rem;">Daftar Siswa</span>
        <input type="text" id="search" class="form-control"
          placeholder="Cari NIS / kelas..."
          style="width:220px;" onkeyup="cari()">
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>No</th>
              <th>NIS</th>
              <th>Kelas</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="tabel-siswa">
            <?php if (empty($siswa)): ?>
              <tr>
                <td colspan="4" style="text-align:center;">Belum ada data siswa</td>
              </tr>
            <?php else: ?>
              <?php $no = 1;
              foreach ($siswa as $s): ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><?= $s['nis'] ?></td>
                  <td><?= htmlspecialchars($s['kelas']) ?></td>
                  <td style="display:flex; justify-content:center;">

                    <!-- EDIT -->
                    <form method="POST" style="display:flex; justify-content:center;">
                      <input type="hidden" name="nis" value="<?= $s['nis'] ?>">
                      <button type="button" class="btn btn-warning"
                        onclick="openEdit(<?= $s['nis'] ?>, '<?= htmlspecialchars($s['kelas'], ENT_QUOTES) ?>')">
                        Edit
                      </button>
                    </form>

                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="modalEdit" onclick="closeEdit()">
    <div class="modal-box" onclick="event.stopPropagation()">

      <h3>Edit Data Siswa</h3>

      <form method="POST">
        <input type="hidden" name="nis" id="edit-nis">

        <div class="form-group">
          <label>NIS</label>
          <input type="text" id="edit-nis-show" class="form-control" readonly>
        </div>

        <div class="form-group">
          <label>Kelas</label>
          <input type="text" name="kelas" id="edit-kelas" class="form-control" required>
        </div>

        <div style="margin-top:10px;display:flex;gap:10px;">
          <button type="submit" name="edit" class="btn btn-primary">Simpan</button>
          <button type="button" onclick="closeEdit()" class="btn btn-secondary">Batal</button>
        </div>
      </form>

    </div>
  </div>
  <?php include_once '../includes/footer.php'; ?>
  <script>
    function showAlert(type, msg) {
      const el = document.getElementById('alert-' + type);
      el.textContent = msg;
      el.classList.add('show');
      setTimeout(() => el.classList.remove('show'), 2500);
    }

    function openEdit(nis, kelas) {
      document.getElementById('edit-nis').value = nis;
      document.getElementById('edit-nis-show').value = nis;
      document.getElementById('edit-kelas').value = kelas;

      document.getElementById('modalEdit').classList.add('show');

      setTimeout(() => {
        document.getElementById('edit-kelas').focus();
      }, 100);
    }

    function closeEdit() {
      document.getElementById('modalEdit').classList.remove('show');
    }

    function cari() {
      let input = document.getElementById("search").value.toLowerCase();
      let rows = document.querySelectorAll("#tabel-siswa tr");

      rows.forEach(row => {
        let nis = row.children[1].textContent.toLowerCase();
        let kelas = row.children[2].textContent.toLowerCase();

        if (nis.includes(input) || kelas.includes(input)) {
          row.style.display = "";
        } else {
          row.style.display = "none";
        }
      });
    }

    // ESC untuk close modal
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeEdit();
      }
    });
  </script>
</body>

</html>