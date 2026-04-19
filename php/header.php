<?php require_once __DIR__ . '/../php/auth.php'; ?>
<!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? 'KantinKu') ?></title>
<link rel="stylesheet" href="<?= $cssPath ?? 'css/style.css' ?>">
</head><body>
<nav class="navbar">
    <a href="index.php" class="brand">🍽️ KantinKu</a>
    <nav>
        <a href="index.php">Menu</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="pesan.php">Pesan</a>
            <a href="riwayat.php">Riwayat</a>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="admin_menu.php">Kelola Menu</a>
                <a href="admin_pesanan.php">Kelola Pesanan</a>
            <?php endif; ?>
            <a href="logout.php">Keluar (<?= htmlspecialchars($_SESSION['user_name']) ?>)</a>
        <?php else: ?>
            <a href="login.php">Masuk</a>
            <a href="register.php">Daftar</a>
        <?php endif; ?>
    </nav>
</nav>
