<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database/init.php';
initDatabase(getDB());
require_once __DIR__ . '/php/auth.php';

$pdo = getDB();
// karena filter WHERE tersedia=1 dihilangkan
$menuList = $pdo->query("SELECT * FROM menu ORDER BY kategori, nama")->fetchAll();

$pageTitle = 'Menu Kantin — KantinKu';
include __DIR__ . '/php/header.php';
?>
<div class="container">
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-<?= $_SESSION['flash']['type'] ?>"><?= htmlspecialchars($_SESSION['flash']['msg']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
    <div class="page-header">
        <h1>🍽️ Menu Kantin</h1>
        <?php if (!empty($_SESSION['user_id']) && $_SESSION['user_role'] !== 'admin'): ?>
        <a href="pesan.php" class="btn btn-primary">🛒 Buat Pesanan</a>
        <?php endif; ?>
    </div>
    <div class="menu-grid">
    <?php foreach ($menuList as $m): ?>
        <div class="menu-card">
            <div class="menu-kategori"><?= htmlspecialchars($m['kategori']) ?></div>
            <div class="menu-nama"><?= htmlspecialchars($m['nama']) ?></div>
            <div class="menu-deskripsi"><?= htmlspecialchars($m['deskripsi'] ?? '') ?></div>
            <div class="menu-harga">Rp <?= number_format($m['harga'], 0, ',', '.') ?></div>
            <?php if (!empty($_SESSION['user_id']) && $_SESSION['user_role'] !== 'admin'): ?>
            <a href="pesan.php?menu=<?= $m['id'] ?>" class="btn btn-primary btn-sm">+ Pesan</a>
            <?php elseif (empty($_SESSION['user_id'])): ?>
            <a href="login.php" class="btn btn-secondary btn-sm">Login untuk pesan</a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>
</div>
</body>
</html>
