<?php
require_once 'config/db.php';
session_start();

if (isset($_SESSION['status_login']) && $_SESSION['status_login'] == true) {
  header('Location: index.php');
  exit;
}

$error = "";

// proses login
if (isset($_POST['login'])) {
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $password = $_POST['password'];

  $query = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username'");
  $data = mysqli_fetch_assoc($query);

  if ($data) {
    if (md5($password) === $data['password']) {
      $_SESSION['status_login'] = true;
      $_SESSION['username'] = $username;
      $_SESSION['role'] = 'admin'; 

      echo "<script>
        alert('Login berhasil');
        window.location.href='index.php';
      </script>";
      exit;
    } else {
      $error = "Password salah";
    }
  } else {
    $error = "Username tidak ditemukan";
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin — E-Aspirasi</title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body class="login-page">

  <?php include_once 'includes/navbar.php'; ?>

  <div class="login-wrap">
    <div class="login-card">
      <div class="login-logo">
        <div class="icon">🏫</div>
        <h2>Login Admin</h2>
        <p>Masuk untuk mengelola aspirasi siswa</p>
      </div>

      <form method="POST">
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>

        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>

        <button type="submit" name="login" class="btn btn-primary"
          style="width:100%;margin-top:0.5rem;">
          Masuk
        </button>
      </form>
    </div>
  </div>

  <?php include_once 'includes/footer.php'; ?>
  <script>
    <?php if (!empty($error)) : ?>
      alert("<?= $error ?>");
    <?php endif; ?>
  </script>
</body>

</html>