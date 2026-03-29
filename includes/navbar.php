<?php $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<nav class="navbar" id="navbar">
    <a href="/index.php" class="nav-brand">E — ASPIRASI SEKOLAH<?= $isAdmin ? '<small style="font-size:0.65rem;opacity:0.6;font-style:normal">(Admin)</small>' : '' ?></a>
    <ul class="nav-links">
        <li><a href="/index.php">Home</a></li>
        <li><a href="/views/input.php">Input Aspirasi</a></li>
        <!-- <li><a href="aspirasi.php" class="active">Aspirasi</a></li> -->
        <li><a href="/views/aspirasi.php">Aspirasi</a></li>

        <?php if ($isAdmin) { ?>
            <li><a href="/views/kategori.php">Kategori</a></li>
            <li><a href="/views/siswa.php">Siswa</a></li>
            <li><a href="/logout.php">Logout</a></li>
        <?php } else { ?>
            <li><a href="/login.php" class="btn-login">Login</a></li>
        <?php } ?>
    </ul>
</nav>