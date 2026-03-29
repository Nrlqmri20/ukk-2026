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
  $nama = trim($_POST['keterangan']);

  if ($nama != '') {
    $nama = mysqli_real_escape_string($conn, $nama);

    $cek = mysqli_query($conn, "SELECT * FROM kategori WHERE ket_kategori='$nama'");
    if (mysqli_num_rows($cek) == 0) {
      mysqli_query($conn, "INSERT INTO kategori (ket_kategori) VALUES ('$nama')");

      echo "<script>
        alert('Kategori berhasil ditambahkan');
        window.location.href='kategori.php';
      </script>";
      exit;
    } else {
      echo "<script>alert('Kategori sudah ada');</script>";
    }
  }
}

// ── EDIT ──
if (isset($_POST['edit'])) {
  $id   = (int) $_POST['id'];
  $nama = trim($_POST['keterangan']);

  if ($nama != '') {
    $nama = mysqli_real_escape_string($conn, $nama);

    mysqli_query($conn, "UPDATE kategori SET ket_kategori='$nama' WHERE id_kategori=$id");

    echo "<script>
      alert('Kategori berhasil diupdate');
      window.location.href='kategori.php';
    </script>";
    exit;
  }
}

// ── HAPUS ──
if (isset($_POST['hapus'])) {
  $id = (int) $_POST['id'];

  $cek = mysqli_query($conn, "SELECT * FROM input_aspirasi WHERE id_kategori=$id");

  if (mysqli_num_rows($cek) == 0) {
    mysqli_query($conn, "DELETE FROM kategori WHERE id_kategori=$id");

    echo "<script>
      alert('Kategori berhasil dihapus');
      window.location.href='kategori.php';
    </script>";
    exit;
  } else {
    echo "<script>alert('Kategori tidak bisa dihapus karena sudah dipakai');</script>";
  }
}

// ── AMBIL DATA ──
$result = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id_kategori DESC");

$kategori = [];
while ($row = mysqli_fetch_assoc($result)) {
  $kategori[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Kelola Kategori</title>
  <link rel="stylesheet" href="../css/style.css">
</head>

<body>

  <?php include '../includes/navbar.php'; ?>

  <div class="container" style="max-width:700px;">
    <div class="page-title">Kelola Kategori</div>

    <!-- TAMBAH -->
    <form method="POST" class="card" style="margin-bottom:1.25rem;">
      <div class="form-group">
        <label>Nama Kategori</label>
        <input type="text" name="keterangan" class="form-control"
          placeholder="Contoh: Sarana Olahraga" required>
      </div>
      <button type="submit" name="tambah" class="btn btn-primary">Tambah</button>
    </form>

    <!-- TABLE -->
    <div class="card" style="padding:0;overflow:hidden;">
      <div class="table-wrap">
        <table class="asp-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Kategori</th>
              <th>Aksi</th>
            </tr>
          </thead>

          <tbody>
            <?php if (empty($kategori)): ?>
              <tr>
                <td colspan="3" style="text-align:center;">Belum ada kategori</td>
              </tr>
            <?php else: ?>
              <?php $no = 1;
              foreach ($kategori as $k): ?>
                <tr>
                  <td><?= $no++ ?></td>

                  <td>
                    <span class="badge-kat">
                      <?= htmlspecialchars($k['ket_kategori']) ?>
                    </span>
                  </td>

                  <td style="display:flex;gap:6px;justify-content:center;">
                    <button class="btn btn-warning"
                      onclick="openEdit(<?= $k['id_kategori'] ?>, '<?= htmlspecialchars($k['ket_kategori'], ENT_QUOTES) ?>')">
                      Edit
                    </button>

                    <button class="btn btn-danger"
                      onclick="openDelete(<?= $k['id_kategori'] ?>, '<?= htmlspecialchars($k['ket_kategori'], ENT_QUOTES) ?>')">
                      Hapus
                    </button>
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

      <h3>Edit Kategori</h3>

      <form method="POST">
        <input type="hidden" name="id" id="edit-id">

        <div class="form-group">
          <label>Nama Kategori</label>
          <input type="text" name="keterangan" id="edit-nama" class="form-control" required>
        </div>

        <div style="margin-top:10px;display:flex;gap:10px;">
          <button type="submit" name="edit" class="btn btn-primary">Simpan</button>
          <button type="button" onclick="closeEdit()" class="btn btn-secondary">Batal</button>
        </div>
      </form>

    </div>
  </div>

  <div class="modal-overlay" id="modalDelete" onclick="closeDelete()">
    <div class="modal-box" onclick="event.stopPropagation()">

      <h3>Hapus Kategori</h3>
      <p>Yakin ingin menghapus <strong id="del-nama"></strong>?</p>

      <form method="POST">
        <input type="hidden" name="id" id="del-id">

        <div style="margin-top:10px;display:flex;gap:10px;">
          <button type="submit" name="hapus" class="btn btn-danger">Hapus</button>
          <button type="button" onclick="closeDelete()" class="btn btn-secondary">Batal</button>
        </div>
      </form>

    </div>
  </div>

  <script>
    function openEdit(id, nama) {
      document.getElementById('edit-id').value = id;
      document.getElementById('edit-nama').value = nama;
      document.getElementById('modalEdit').classList.add('show');

      setTimeout(() => {
        document.getElementById('edit-nama').focus();
      }, 100);
    }

    function closeEdit() {
      document.getElementById('modalEdit').classList.remove('show');
    }

    function openDelete(id, nama) {
      document.getElementById('del-id').value = id;
      document.getElementById('del-nama').textContent = nama;
      document.getElementById('modalDelete').classList.add('show');
    }

    function closeDelete() {
      document.getElementById('modalDelete').classList.remove('show');
    }
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeEdit();
        closeDelete();
      }
    });
  </script>
</body>

</html>