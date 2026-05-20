<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database/init.php';
initDatabase(getDB());
require_once __DIR__ . '/php/auth.php';

$pdo = getDB();

$kategori = $_GET['kategori'] ?? '';

if ($kategori != '') {
    $stmt = $pdo->prepare(
        "SELECT * FROM menu
         WHERE tersedia = 1
         AND kategori = ?
         ORDER BY nama"
    );

    $stmt->execute([$kategori]);
    $menuList = $stmt->fetchAll();
    //fix bug no 5
} else {
    $menuList = $pdo->query(
        "SELECT * FROM menu
         WHERE tersedia = 1
         ORDER BY kategori, nama"
    )->fetchAll();
}

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
    <form method="GET" style="margin-bottom:20px;">
    <label>Pilih Kategori:</label>

    <select name="kategori">
        <option value="">Semua</option>
        <option value="Makanan" <?= $kategori=='Makanan' ? 'selected' : '' ?>>Makanan</option>
        <option value="Minuman" <?= $kategori=='Minuman' ? 'selected' : '' ?>>Minuman</option>
        <option value="Snack" <?= $kategori=='Snack' ? 'selected' : '' ?>>Snack</option>
    </select>

    <button type="submit" class="btn btn-primary">
        Filter
    </button>
    </form>
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
