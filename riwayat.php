<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database/init.php';
initDatabase(getDB());
require_once __DIR__ . '/php/auth.php';
requireLogin();

$pdo = getDB();
// BUG 6: Query mengambil semua pesanan — tidak difilter berdasarkan user_id
// sehingga pelanggan bisa melihat pesanan milik user lain
$pesananList = $pdo->query("SELECT p.*, u.name AS pelanggan FROM pesanan p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC")->fetchAll();

$statusLabel = [
    'pending'    => ['label'=>'Menunggu',  'class'=>'badge-warning'],
    'diproses'   => ['label'=>'Diproses',  'class'=>'badge-info'],
    'siap'       => ['label'=>'Siap Ambil','class'=>'badge-success'],
    'selesai'    => ['label'=>'Selesai',   'class'=>'badge-secondary'],
    'dibatalkan' => ['label'=>'Dibatalkan','class'=>'badge-danger'],
];

$pageTitle = 'Riwayat Pesanan — KantinKu';
include __DIR__ . '/php/header.php';
?>
<div class="container">
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-<?= $_SESSION['flash']['type'] ?>"><?= htmlspecialchars($_SESSION['flash']['msg']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
    <div class="page-header"><h1>📋 Riwayat Pesanan</h1><a href="pesan.php" class="btn btn-primary">+ Pesan Lagi</a></div>
    <?php if (empty($pesananList)): ?>
        <div class="card"><div class="card-body" style="text-align:center;padding:2rem;">Belum ada pesanan.</div></div>
    <?php else: ?>
    <?php foreach ($pesananList as $p): ?>
    <?php $sl = $statusLabel[$p['status']] ?? ['label'=>$p['status'],'class'=>'badge-secondary']; ?>
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <span>Pesanan #<?= $p['id'] ?> — <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></span>
            <span class="badge <?= $sl['class'] ?>"><?= $sl['label'] ?></span>
        </div>
        <div class="card-body" style="padding:0;">
            <?php
            $items = $pdo->prepare("SELECT pi.*, m.nama FROM pesanan_item pi JOIN menu m ON pi.menu_id = m.id WHERE pi.pesanan_id = ?");
            $items->execute([$p['id']]);
            ?>
            <table>
                <thead><tr><th>Menu</th><th>Qty</th><th>Subtotal</th></tr></thead>
                <tbody>
                <?php foreach ($items->fetchAll() as $item): ?>
                <tr><td><?= htmlspecialchars($item['nama']) ?></td><td><?= $item['jumlah'] ?></td><td>Rp <?= number_format($item['harga']*$item['jumlah'],0,',','.') ?></td></tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot><tr><td colspan="2" style="text-align:right;font-weight:700;">Total</td><td style="font-weight:700;color:#e67e22;">Rp <?= number_format($p['total_harga'],0,',','.') ?></td></tr></tfoot>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
