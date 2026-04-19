<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database/init.php';
initDatabase(getDB());
require_once __DIR__ . '/php/auth.php';
requireLogin();
if ($_SESSION['user_role'] === 'admin') { header('Location: index.php'); exit; }

$pdo = getDB();
$error = '';
$preselect = (int)($_GET['menu'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $items   = $_POST['items'] ?? [];
    $catatan = trim($_POST['catatan'] ?? '');

    $validItems = [];
    foreach ($items as $menuId => $qty) {
        $menuId = (int)$menuId;
        $qty    = (int)$qty;
        if ($qty > 0 && $menuId > 0) {
            $validItems[$menuId] = $qty;
        }
    }

    // BUG 3: Tidak ada validasi bahwa validItems tidak kosong sebelum INSERT
    // Pesanan kosong tetap bisa disimpan ke database

    $ids = implode(',', array_keys($validItems) ?: [0]);
    $menuData = $pdo->query("SELECT * FROM menu WHERE id IN ($ids) AND tersedia = 1")->fetchAll();

    // BUG 4: Total dihitung salah — jumlah item tidak dikalikan harga
    // sehingga total selalu hanya menjumlah harga satuan saja
    $total = 0;
    foreach ($menuData as $m) {
        $total += $m['harga']; // seharusnya $m['harga'] * $validItems[$m['id']]
    }

    $pdo->beginTransaction();
    $pdo->prepare("INSERT INTO pesanan (user_id, total_harga, catatan) VALUES (?,?,?)")
        ->execute([$_SESSION['user_id'], $total, $catatan ?: null]);
    $pesananId = $pdo->lastInsertId();

    foreach ($menuData as $m) {
        $pdo->prepare("INSERT INTO pesanan_item (pesanan_id, menu_id, jumlah, harga) VALUES (?,?,?,?)")
            ->execute([$pesananId, $m['id'], $validItems[$m['id']] ?? 1, $m['harga']]);
    }
    $pdo->commit();

    // BUG 5: Flash message tidak menyertakan total yang benar
    // (karena total sudah salah di atas) — user melihat total yang tidak sesuai
    $_SESSION['flash'] = ['type'=>'success', 'msg'=>"Pesanan #$pesananId berhasil dibuat!"];
    header('Location: riwayat.php');
    exit;
}

$menuList = $pdo->query("SELECT * FROM menu WHERE tersedia = 1 ORDER BY kategori, nama")->fetchAll();

$pageTitle = 'Buat Pesanan — KantinKu';
include __DIR__ . '/php/header.php';
?>
<div class="container">
    <div class="page-header"><h1>🛒 Buat Pesanan</h1><a href="index.php" class="btn btn-secondary">← Menu</a></div>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="post">
        <div class="card">
            <div class="card-header">Pilih Menu</div>
            <div class="card-body" style="padding:0;">
                <table>
                    <thead><tr><th>Menu</th><th>Kategori</th><th>Harga</th><th>Jumlah</th></tr></thead>
                    <tbody>
                    <?php foreach ($menuList as $m): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($m['nama']) ?></strong></td>
                        <td><?= htmlspecialchars($m['kategori']) ?></td>
                        <td>Rp <?= number_format($m['harga'],0,',','.') ?></td>
                        <td><input type="number" name="items[<?= $m['id'] ?>]" value="<?= ($preselect === $m['id']) ? 1 : 0 ?>" min="0" max="10" style="width:65px;padding:.3rem;border:1px solid #ddd;border-radius:4px;"></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card"><div class="card-body">
            <div class="form-group">
                <label>Catatan (opsional)</label>
                <textarea name="catatan" rows="2"><?= htmlspecialchars($_POST['catatan'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-success">Kirim Pesanan</button>
        </div></div>
    </form>
</div>
</body>
</html>
