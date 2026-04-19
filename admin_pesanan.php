<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database/init.php';
initDatabase(getDB());
require_once __DIR__ . '/php/auth.php';
requireAdmin();

$pdo = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $allowed = ['pending','diproses','siap','selesai','dibatalkan'];
    if ($id > 0 && in_array($status, $allowed)) {
        $pdo->prepare("UPDATE pesanan SET status = ? WHERE id = ?")->execute([$status, $id]);
        $msg = 'Status pesanan diperbarui.';
    }
}

$pesananList = $pdo->query("SELECT p.*, u.name AS pelanggan FROM pesanan p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC")->fetchAll();

$statusLabel = [
    'pending'   => ['label'=>'Menunggu',  'class'=>'badge-warning'],
    'diproses'  => ['label'=>'Diproses',  'class'=>'badge-info'],
    'siap'      => ['label'=>'Siap Ambil','class'=>'badge-success'],
    'selesai'   => ['label'=>'Selesai',   'class'=>'badge-secondary'],
    'dibatalkan'=> ['label'=>'Dibatalkan','class'=>'badge-danger'],
];

$pageTitle = 'Kelola Pesanan — KantinKu';
include __DIR__ . '/php/header.php';
?>
<div class="container">
    <div class="page-header"><h1>Kelola Pesanan</h1></div>
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <?php foreach ($pesananList as $p): ?>
    <?php $sl = $statusLabel[$p['status']] ?? ['label'=>$p['status'],'class'=>'badge-secondary']; ?>
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <span>#<?= $p['id'] ?> — <?= htmlspecialchars($p['pelanggan']) ?> — <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></span>
            <div style="display:flex;gap:.5rem;align-items:center;">
                <span class="badge <?= $sl['class'] ?>"><?= $sl['label'] ?></span>
                <form method="post" style="display:flex;gap:.3rem;">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <select name="status" style="padding:.3rem;border:1px solid #ddd;border-radius:4px;font-size:.85rem;">
                        <?php foreach ($statusLabel as $val => $lbl): ?>
                        <option value="<?= $val ?>" <?= $p['status'] === $val ? 'selected' : '' ?>><?= $lbl['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">OK</button>
                </form>
            </div>
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
            <?php if ($p['catatan']): ?>
            <div style="padding:.75rem 1rem;font-size:.88rem;color:#777;">📝 <?= htmlspecialchars($p['catatan']) ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
</body>
</html>
